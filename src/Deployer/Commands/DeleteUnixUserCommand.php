<?php


namespace rezozero\Deployer\Commands;

use rezozero\Deployer\Controllers\UnixUser;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

class DeleteUnixUserCommand extends ConfigurationAwareCommand
{
    /**
     * @inheritDoc
     */
    protected function needsPrivileges()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        parent::configure();

        $this
            // the name of the command (the part after "bin/console")
            ->setName('user:remove')

            // the short description shown while running "php bin/console list"
            ->setDescription('Delete a unix user and its home folder <info>[Must be run as root]</info>.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to delete a unix user...')
            ->addArgument('username', InputArgument::REQUIRED, 'User name to delete.')
        ;
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $config = $this->getConfiguration();

        $username = $input->getArgument('username');
        $unixUser = new UnixUser($username, '', $config);

        $question = new ConfirmationQuestion("<question>Are you sure to delete \"$username\" user and ALL ITS FILES?</question> [y|N]", false);
        if ($this->getHelper('question')->ask($input, $output, $question) === false) {
            return false;
        }

        if (!$unixUser->userExists()) {
            throw new InvalidArgumentException('User does not exist.');
        }

        $unixUser->delete();

        $fs = new Filesystem();
        if (false === $fs->remove($unixUser->getHomeFolder())) {
            throw new InvalidArgumentException('Impossible to remove "'. $unixUser->getHomeFolder() . '" home folder.');
        }

        $output->writeln("User: <info>$username</info> has been deleted.");
    }
}