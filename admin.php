<?php

if (!defined('DOKU_INC')) die();
use \dokuwiki\Form\Form;

class admin_plugin_pageburner extends DokuWiki_Admin_Plugin {
    /**
     * @var INPUT INPUT
     */
    public function handle() {
        global $INPUT;
        if ($INPUT->str('act') != 'burn') {
            return;
        }
        $years = $this->prepareScale($INPUT->str('year'));
        $series = $this->prepareScale($INPUT->str('series'));

        $template = $this->getTemplate();

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
        if (count($series) == 0) {
            if (in_array('@series@', $allParam)) {
                msg('empty param series', -1);
                return;
            }
            $series[] = null;
        }
        if (count($years) == 0) {
            if (in_array('@year@', $allParam)) {
                msg('empty param year', -1);
                return;
            }
            $years[] = null;
        }
        foreach ($years as $year) {
            foreach ($series as $simpleSeries) {
                $pageID = str_replace(['@year@', '@series@'], [$year, $simpleSeries], $pagePath);
                $currentPagePath = wikiFN($pageID);
                $currentContent = str_replace(['@year@', '@series@'], [$year, $simpleSeries], $template);
                io_saveFile($currentPagePath, $currentContent);
                msg('stránka <a href="' . wl($pageID) . '">' . $pageID . '</a> bola vypálená', 1);
            }
        }
    }

    public function html() {
        echo '<h1>Page Burner</h1>';
        $form = new Form();
        $form->setHiddenField('act', 'burn');
        $form->addFieldsetOpen('Template');

        $templatePages = json_decode(io_readFile(__DIR__ . '/pages.json'));
        array_unshift($templatePages, '--vybrať template--');
        $form->addDropdown('template_page', array_map(function ($row) {
            return $row->name;
        }, $templatePages))->attrs(['data-data' => json_encode($templatePages), 'id' => 'page-burner_template-page']);
        $form->addTextarea('template');
        $form->addFieldsetClose();

        $form->addFieldsetOpen('Parametre');
        $form->addTextInput('page_path', $this->getLang('page_path'))->addClass('block');
        $form->addTextInput('year', $this->getLang('year'))->addClass('block');
        $form->addTextInput('series', $this->getLang('series'))->addClass('block');

        $form->addFieldsetClose();
        $form->addButton('submit', 'Burn');

        echo $form->toHTML();
    }

    private function prepareScale($scaleString) {
        if ($scaleString == '') {
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

    private function getTemplate() {
        global $INPUT;
        $template = '';

        if ($INPUT->str('template')) {
            $template = $INPUT->str('template');
        }
        return $template;
    }
}
