<?php


namespace rezozero\Deployer\Controllers;
use rezozero\Deployer\VHosts\AbstractHostFile;


/**
 * Class Kernel
 * @package rezozero\Deployer\Controllers
 */
class Kernel
{
    /**
     * @var Kernel|null
     */
    private static $instance = null;
    /**
     * @var Configuration
     */
    private $configuration;
    private $twigLoader;
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var UnixUser
     */
    private $unixUser;
    /**
     * @var Database
     */
    private $database;
    private $color;
    /**
     * @var AbstractHostFile
     */
    private $vhost;
    private $mail;

    /**
     * Kernel constructor.
     */
    private function __construct()
    {
        $this->configuration = new Configuration();
        $this->twigLoader = new \Twig_Loader_Filesystem(APP_ROOT.'/views');
        $this->twig = new \Twig_Environment($this->twigLoader);
        $this->color = new Color();
        $this->mail = new \PHPMailer;
    }
    /**
     * Wrap Twig_Environment::render method
     * @param  string $template Relative path to template
     * @param  array $vars     Variables to inject into template
     * @return string Output content
     */
    public function render( $template, &$vars )
    {
        return $this->twig->render($template, $vars);
    }

    public function run()
    {
        echo $this->getSplash();

        if ($this->configuration->setConfigFile( APP_ROOT.'/conf/config.json' ) !== false) {
            if ($this->configuration->verifyConfiguration()) {

                /*
                 * Ask user for data
                 */
                $this->configuration->requestHostname();
                $this->configuration->requestUsername();

                /*
                 * Get the right virtual host builder
                 */
                $class = $this->configuration->getVHostClass();
                if ($class !== false) {

                    $this->unixUser = new UnixUser( $this->configuration->getUsername() );
                    $this->database = new Database( $this->configuration->getUsername() );
                    $this->vhost = new $class();

                    /*
                     * Check if files already exist
                     */
                    if (!$this->unixUser->userExists() &&
                        $this->vhost->isWritable() &&
                        !$this->vhost->virtualHostExists() &&
                        !$this->vhost->poolFileExists()) {

                        /*
                         * Begin creation
                         */
                        if ($this->unixUser->createUser() === false) {
                            echo $this->getColor()->getColoredString("[ERROR] Unable to create unix user.", 'red', null).PHP_EOL;
                            exit;
                        }
                        $this->unixUser->createFileStructure();

                        if ($this->database->createUserDatabase() === false) {
                            echo $this->getColor()->getColoredString("[ERROR] Unable to create mysql database.", 'red', null).PHP_EOL;
                        }


                        if ($this->vhost->generateVirtualHost() === false) {
                            echo $this->getColor()->getColoredString("[ERROR] Unable to generate virtual host file.", 'red', null).PHP_EOL;
                        }
                        if ($this->vhost->generatePHPPool() === false) {
                            echo $this->getColor()->getColoredString("[ERROR] Unable to generate php-fpm pool file.", 'red', null).PHP_EOL;
                        }
                        if ($this->vhost->enableVirtualHost() === false) {
                            echo $this->getColor()->getColoredString("[ERROR] Unable to enable virtual host file. You'll need to do it manually…", 'red', null).PHP_EOL;
                        }
                        if ($this->vhost->restartServers() === false) {
                            echo $this->getColor()->getColoredString("[ERROR] Unable to restart web servers.", 'red', null).PHP_EOL;
                        }

                        echo PHP_EOL.$this->getSummary();
                        $this->mailSummary();

                        return true;
                    }
                    else {
                        echo $this->getColor()->getColoredString("[ERROR] User or virtual host or php pool already exists!.", 'red', null).PHP_EOL;
                    }

                }
                else {
                    echo $this->getColor()->getColoredString("[ERROR] Unable to get Virtual Host writer class.", 'red', null).PHP_EOL;
                }
            }
        }
        else {
            echo "[ERROR] Unable to load configuration file (".APP_ROOT."/conf/config.json).".PHP_EOL;
        }
    }

    public function getColor()
    {
        return $this->color;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getSplash()
    {
        $msg = "";
        for ($i=0; $i < 22; $i++) {
            $msg .= $this->getColor()->getColoredString("|", 'green', 'green');
            $msg .= $this->getColor()->getColoredString("|", 'green', 'white');
        }
        $msg .= $this->getColor()->getColoredString("|", 'green', 'green');
        $msg .= "".PHP_EOL;
        $msg .= $this->getColor()->getColoredString("                                             ", 'black', 'green').PHP_EOL;
        $msg .= $this->getColor()->getColoredString("              RZ Deployer                    ", 'boldblack', 'green').PHP_EOL;
        $msg .= $this->getColor()->getColoredString("              REZO ZERO                      ", 'black', 'green').PHP_EOL;
        $msg .= $this->getColor()->getColoredString("              Ambroise Maupate               ", 'black', 'green').PHP_EOL;
        $msg .= $this->getColor()->getColoredString("                                             ", 'black', 'green').PHP_EOL;
        for ($i=0; $i < 22; $i++) {
            $msg .= $this->getColor()->getColoredString("|", 'green', 'green');
            $msg .= $this->getColor()->getColoredString("|", 'green', 'white');
        }
        $msg .= $this->getColor()->getColoredString("|", 'green', 'green');
        $msg .= "".PHP_EOL.PHP_EOL;

        return $msg;
    }

    public function getSummary()
    {
        $msg = "";
        $msg .= "=========================================".PHP_EOL;
        $msg .= "================= RZ Deployer =============".PHP_EOL;
        $msg .= "=========================================".PHP_EOL.PHP_EOL;


        $msg .= $this->configuration->getHostname()." is now deployed on your webserver.".PHP_EOL.PHP_EOL;
        $msg .= "Hostname: ".$this->configuration->getHostname().PHP_EOL;

        $msg .= "SSH user: ".$this->unixUser->getName().PHP_EOL;
        $msg .= "SSH password: ".$this->unixUser->getPassword().PHP_EOL;
        $msg .= "SSH path: ".$this->unixUser->getHome().PHP_EOL.PHP_EOL;

        $msg .= "MySQL database: ".$this->database->getDatabase().PHP_EOL;
        $msg .= "MySQL user: ".$this->database->getUser().PHP_EOL;
        $msg .= "MySQL password: ".$this->database->getPassword().PHP_EOL.PHP_EOL;

        $msg .= "=========================================".PHP_EOL;

        return $msg;
    }

    /**
     * @return UnixUser
     */
    public function getUser()
    {
        return $this->unixUser;
    }

    /**
     * Send a mail summary for you setup
     */
    public function mailSummary()
    {
        $confData = $this->configuration->getData();

        $summary = array(
            'time'=>time(),
            'hostname'=>$this->configuration->getHostname(),
            'user' => array(
                'name'=>    $this->unixUser->getName(),
                'password'=>$this->unixUser->getPassword(),
                'path'=>    $this->unixUser->getHome()
            ),
            'database'=> array(
                'user'=>    $this->database->getUser(),
                'password'=>$this->database->getPassword(),
                'database'=>$this->database->getDatabase(),
                'host'=>	$confData['mysql_host']
            ),
            'virtualhosts'=> array (
                'type'=>    $confData['webserver_type'],
                'vhost'=>   $this->vhost->getVHostFile(),
                'phppool'=> $this->vhost->getPoolFile()
            )
        );

        if (filter_var($confData['sender_email'], FILTER_VALIDATE_EMAIL) !== false) {

            $this->mail->From = $confData['sender_email'];
            $this->mail->FromName = "RZ Deployer";

            $emails = "";

            if (is_array($confData['notification_email'])) {
                foreach ($confData['notification_email'] as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
                        $this->mail->addAddress($email);     // Add a recipient
                        $emails .= $email.", ";
                    }
                }
            } elseif (filter_var($confData['notification_email'], FILTER_VALIDATE_EMAIL) !== false) {
                $this->mail->addAddress($confData['notification_email']);     // Add a recipient
                $emails .= $confData['notification_email'];
            }

            $this->mail->addReplyTo($confData['sender_email'], "RZ Deployer");

            $this->mail->isHTML(true);                                  // Set email format to HTML

            $this->mail->Subject = $this->configuration->getHostname()." is now deployed";
            $this->mail->Body    = $this->render('email.html.twig', $summary);
            $this->mail->AltBody = $this->getSummary();

            if(!$this->mail->send()) {
                echo $this->getColor()->getColoredString('Message could not be sent.').PHP_EOL;
                echo $this->getColor()->getColoredString('Mailer Error: ' . $this->mail->ErrorInfo).PHP_EOL;
            } else {
                echo Kernel::getInstance()->getColor()->getColoredString('Summary message has been sent to '.$emails, null, 'green').PHP_EOL.PHP_EOL;
            }
        }
    }

    /**
     * @return Kernel
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new Kernel();
        }

        return static::$instance;
    }
}
