<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\MandangoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Mandango\MandangoBundle\Util;

/**
 * GenerateCommand.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class GenerateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mandango:generate')
            ->setDescription('Generate classes from config classes');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('processing config classes');

        //fuck this
        //$modelDir = $this->getContainer()->getParameter('mandango.model_dir');

        $configClasses = array();
        // application + extra
        /* actually fuck these
        foreach (array_merge(
            array($this->getContainer()->getParameter('kernel.root_dir').'/config/mandango'),
            $this->getContainer()->getParameter('mandango.extra_config_classes_dirs')
        ) as $dir) {
            if (is_dir($dir)) {
                $finder = new Finder();
                foreach ($finder->files()->name('*.yml')->followLinks()->in($dir) as $file) {
                    foreach ((array) Yaml::parse($file) as $class => $configClass) {
                        // class
                        if (0 === strpos($class, 'Model\\')) {
                            throw new \RuntimeException('Putting classes into a "Model\" namespace sounds like a fucking retarded idea.');
                        }
                        $output->writeln("\tprocessing class $class");

                        // config class
                        $configClass['output'] = $modelDir.'/'.str_replace('\\', '/', substr(substr($class, 0, strrpos($class, '\\')), 6));
                        $configClass['bundle_name']      = null;
                        $configClass['bundle_namespace'] = null;
                        $configClass['bundle_dir']       = null;

                        $configClasses[$class] = $configClass;
                    }
                }
            }
        }
        */

        // bundles
        $configClassesPending = array();
        foreach ($this->getContainer()->get('kernel')->getBundles() as $bundle) {
            $bundleModelNamespace = $bundle->getNamespace() . '\\Model';
            $modelDir = $bundle->getPath() . '/Model';

            if (is_dir($dir = $bundle->getPath() . '/Resources/config/mandango')) {
                $finder = new Finder();
                foreach ($finder->files()->name('*.yml')->followLinks()->in($dir) as $file) {
                    foreach ((array)Yaml::parse($file) as $class => $configClass) {
                        // class
                        $output->writeln("\tprocessing class $class");
                        if (0 === strpos($class, 'Model\\')) {
                            throw new \RuntimeException('Putting classes into a "Model\" namespace sounds like a fucking retarded idea.');
                        }
                        if (0 !== strpos($class, $bundleModelNamespace)) {
                            $output->writeln("**** What is wrong with class $class? Where does it belong to?");
                            unset($configClass['output'], $configClass['bundle_name'], $configClass['bundle_dir']);
                            $configClassesPending[] = array('class' => $class, 'config_class' => $configClass);
                            continue;
                        }

                        // config class
                        $configClass['output'] = $modelDir;
                        $configClass['bundle_name'] = $bundle->getName();
                        $configClass['bundle_namespace'] = $bundle->getNamespace();
                        $configClass['bundle_dir'] = $bundle->getPath();

                        //$classSpaced = $bundle->getNamespace()
                        if (isset($configClasses[$class])) {
                            $configClasses[$class] = Util::arrayDeepMerge($configClasses[$class], $configClass);
                        } else {
                            $configClasses[$class] = $configClass;
                        }
                    }
                }
            }
        }

        // merge bundles
        foreach ($configClassesPending as $pending) {
            if (!isset($configClasses[$pending['class']])) {
                throw new \RuntimeException(sprintf('The class "%s" does not exist.', $pending['class']));
            }

            $configClasses[$pending['class']] = Util::arrayDeepMerge(
                $pending['config_class'],
                $configClasses[$pending['class']]
            );
        }

        $output->writeln('generating classes');

        $mondator = $this->getContainer()->get('mandango.mondator');
        $mondator->setConfigClasses($configClasses);
        $mondator->process();
    }
}
