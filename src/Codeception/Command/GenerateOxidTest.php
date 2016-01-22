<?php
/**
 * Created by solutionDrive GmbH.
 *
 * @author   :    Matthias Alt <alt@solutionDrive.de>
 * @date     :      21.01.16
 * @time     :      17:26
 * @copyright: 2016 solutionDrive GmbH
 */

namespace Codeception\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateOxidTest extends Base
{
    protected $template  = <<<EOF
<?php
%s

%s %sCest
{

    public function _before()
    {
    }

    public function _after()
    {
    }

    // tests
    %s

}
EOF;
    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('suite', InputArgument::REQUIRED, 'suite where tests will be put'),
            new InputArgument('class', InputArgument::REQUIRED, 'test name'),
            new InputArgument('module', InputArgument::REQUIRED, 'module name'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    public function getDescription() {
        return 'Generates empty Test-File of given Class in given Oxid-Module and Suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_initOxidFrameWork();
        $suite = $input->getArgument('suite');
        $class = $input->getArgument('class');
        $module = $input->getArgument('module');


        $sPathToModulesDir = \oxRegistry::getConfig()->getModulesDir(true);
        $sModulePath = \oxRegistry::get('oxModule')->getModulePath($module);
        $sFullPath = $sPathToModulesDir.$sModulePath."/tests/".$suite."/";
        $output->writeln($sFullPath);


        $suiteconf = $this->getSuiteConfig($suite, $input->getOption('config'));

        $guy = $suiteconf['class_name'];

        $classname = $this->getClassName($class);
        $path = $this->buildPath($sFullPath, $class);
        $ns = $this->getNamespaceString($suiteconf['namespace'].'\\'.$class);
        $ns .= "use ".$suiteconf['namespace'].'\\'.$guy.";";

        $filename = $this->completeSuffix($classname, 'Cest');
        $filename = $path.$filename;

        if (file_exists($filename)) {
            $output->writeln("<error>Test $filename already exists</error>");
            exit;
        }

        $classname = $this->removeSuffix($classname, 'Cest');

        $tests = sprintf($this->methodTemplate, "tryToTest", $guy, '$I');

        $res = $this->save($filename, sprintf($this->template, $ns, 'class', $classname, $tests));
        if (!$res) {
            $output->writeln("<error>Test $filename already exists</error>");
            return;
        }

        $output->writeln("<info>Test was created in $filename</info>");

    }

    /**
     * init the Shop-Framework to be able to load OXID-specific classes
     * @return void
     */
    protected function _initOxidFramework()
    {
        if (class_exists('oxRegistry')) {
            return;
        }
        $sPathToBootstrap = getcwd() . DIRECTORY_SEPARATOR . "bootstrap.php";
        if (file_exists($sPathToBootstrap)) {
            include_once $sPathToBootstrap;
        }
    }
}
