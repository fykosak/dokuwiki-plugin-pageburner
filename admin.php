<?php


if (!defined('DOKU_INC')) die();

class admin_plugin_pageburner extends DokuWiki_Admin_Plugin {
    /**
     * @var INPUT INPUT
     */
    public function handle() {
        global $INPUT;
        $years = $this->prepareScale($INPUT->str('year'));
        $series = $this->prepareScale($INPUT->str('series'));
        $template = "";
        $templatePath = $INPUT->str('template_path');
        if ($templatePath) {
            if (!page_exists($templatePath)) {
                msg('page ' . $templatePath . ' does\'nt exist ');
            }
            $template = io_readFile(wikiFN($templatePath));
        }

        if ($INPUT->str('template')) {
            $template = $INPUT->str('template');
        }
        if (!$template) {
            msg('empty template', -1);
            return;
        }
        $pagePath = $INPUT->str('page_path');
        if (!$pagePath) {
            msg('empty page Path', -1);
            return;
        }
        $allParam = $this->getParams($template, $INPUT->str('page_path'));
        if (in_array('@series@', $allParam) && count($series) == 0) {
            msg('empty param series', -1);
            return;
        }
        if (in_array('@year@', $allParam) && count($years) == 0) {
            msg('empty param year', -1);
            return;
        }

    }

    public function html() {
        $form = new \dokuwiki\Form\Form();
        $form->addFieldsetOpen("Template");
        $templatePages = [];
        $form->addDropdown('template_page', $templatePages);
        $form->addTextarea('template');
        $form->addFieldsetClose();


        $form->addFieldsetOpen("Parametre");
        $form->addTextInput('page_path', $this->getLang('page_path'));
        $form->addTextInput('year', $this->getLang('year'));
        $form->addTextInput('series', $this->getLang('series'));

        $form->addFieldsetClose();
        $form->addButton('submit', 'Burn');

        echo $form->toHTML();
    }

    private function prepareScale($scaleString) {
        if ($scaleString == "") {
            return [];
        }
        $scales = explode(',', $scaleString);

        foreach ($scales as $key => $scale) {
            if (preg_match('/([0-9]+)-([0-9]+)/', $scale, $ms)) {
                unset($scales[$key]);
                $range = range($ms[1], $ms[2]);
                $scales = array_merge($scales, $range);
            }
        }
        $scales = array_map(function ($value) {
            return (int)$value;
        }, $scales);;
        return array_unique($scales);
    }

    private function getParams($template, $pagePath) {
        $reqExp = '/(@[a-z]+@)/';
        preg_match_all($reqExp, $template, $templateParam);
        preg_match_all($reqExp, $pagePath, $pageParam);
        $allParam = array_unique(array_merge($templateParam[1], $pageParam[1]));
        return $allParam;

    }
}