<?php

namespace dokuwiki\plugin\station_termine\meta;

use dokuwiki\plugin\struct\meta\Column;
use dokuwiki\plugin\struct\meta\SearchConfig;
use dokuwiki\plugin\struct\meta\StructException;
use dokuwiki\plugin\struct\meta\Value;
use dokuwiki\plugin\struct\types\Color;
use dokuwiki\plugin\struct\types\Date;
use dokuwiki\plugin\struct\types\DateTime;

class StatCal {

    /** @var string the Type of renderer used */
    protected $mode;

    /** @var \Doku_Renderer the DokuWiki renderer used to create the output */
    protected $renderer;

    /** @var SearchConfig the configured search - gives access to columns etc. */
    protected $searchConfig;

    /** @var Column[] the list of columns to be displayed */
    protected $columns;

    /** @var  Value[][] the search result */
    protected $result;

    /** @var int number of all results */
    protected $resultCount;

    /**
     * @var string[] the result PIDs for each row
     */
    protected $resultPIDs;


    /**
     * Initialize the Aggregation renderer and executes the search
     *
     * You need to call @see render() on the resulting object.
     *
     * @param string $id
     * @param string $mode
     * @param \Doku_Renderer $renderer
     * @param SearchConfig $searchConfig
     */
    public function __construct($id, $mode, \Doku_Renderer $renderer, SearchConfig $searchConfig) {
        $this->mode = $mode;
        $this->renderer = $renderer;
        $this->searchConfig = $searchConfig;
        $this->columns = $searchConfig->getColumns();
        $this->result = $this->searchConfig->execute();
        $this->resultCount = $this->searchConfig->getCount();
        $this->resultPIDs = $this->searchConfig->getPids();

        $conf = $searchConfig->getConf();
        //$this->skipWeekends = $conf['skipweekends'];

        $this->key = array();
        foreach ($this->columns as $colnum => $col) {
            $this->key[$col->getLabel()] = $colnum;
        }

        // filtern des result nach jahr oder aktuellem datum
        // sortieren nach datum
    }


    /**
     * Output the calendar. Called by syntax_plugin_struct_table
     */
    public function render() {
        /* if($this->mode !== 'xhtml') { */
        /*     $this->renderer->cdata('no other renderer than xhtml supported for struct gantt'); */
        /*     return; */
        /* } */
       
        if ($this->mode === 'xhtml') $this->renderer->doc .= "<div class=\"station-termin-table\">";

        $this->renderer->table_open();
        $this->renderer->tablethead_open();
        //$this->renderColumnHeaders();
        $this->renderer->tablethead_close();
        $this->renderer->tabletbody_open();

        uasort($this->result, function($a,$b){
            return strcmp($this->readRaw($a,"start"), $this->readRaw($b,"start"));
        });
        
        foreach($this->result as $rownum => $row) {
            $this->renderResultRow($rownum, $row);
        }
        $this->renderer->tabletbody_close();
        $this->renderer->table_close();

        if ($this->mode === 'xhtml') $this->renderer->doc .= "</div>";
        
        /* $this->renderer->section_open(2); */
        /* $this->renderer->header("columns", 2, 0); */
        /* $this->renderer->code(print_r($this->columns, true)); */
        /* $this->renderer->section_close();         */
        /* foreach($this->result as $rownum => $row) { */
        /*     $this->renderer->section_open(2); */
        /*     $this->renderer->header("{$row[$this->key["start"]]->getDisplayValue()} row: $rownum pid:$pid", 2, 0); */
        /*     $this->renderer->code(print_r($row, true)); */
        /*     $this->renderer->section_close();         */
        /* } */
    }

    protected function readRaw($row, $field) {
        if (isset($this->key[$field]) && isset($row[$this->key[$field]])) {
            return $row[$this->key[$field]]->getRawValue();
        } else {
            return '';
        }
    }

    protected function readDateTime($row, $field, $format) {
        $date = date_create($this->readRaw($row, $field));
        if($date !== false) {
            return date_format($date, $format);
        } else {
            return '';
        }
    }

    /**
     * Render a single result row
     *
     * @param int $rownum
     * @param array $row
     */
    protected function renderResultRow($rownum, $row) {
        // TODO add span or div with data-field="schemaname.fieldname" for inline editor
        
        // table-row mit CSS style entsprechend [thema] um die Farbe anzupassen
        // TODO handle multiple correctly!
        $thema = $this->readRaw($row, "thema");
        $this->renderer->tablerow_open("station-thema-$thema station-termin-foldable");

        // add data attribute
        if($this->mode == 'xhtml') {
            $pid = $this->resultPIDs[$rownum];
            $this->renderer->doc = substr(rtrim($this->renderer->doc), 0, -1); // remove closing '>'
            $this->renderer->doc .= ' data-pid="'.hsc($pid).'">';
        }


        // col: Wann? [start]-[ende] zusammengeführt, [datehint] [startzeit]-[endzeit]
        // datehint ist z.B. "täglich", "montags"
        // colspan, align, rowspan, classes
        $this->renderer->tablecell_open(1, "left");
        /* list($start_year, $start_month, $start_day) = */
        /*     explode('-', $this->readDateTime($row, "start", "Y-m-d"), 3); */
        /* list($end_year, $end_month, $end_day) = */
        /*     explode('-', $this->readDateTime($row, "ende", "Y-m-d"), 3); */
        /* if ($start_day == $end_day && $start_month == $end_month && $start_year == $end_year) { */
        /*     $this->renderer->cdata("$start_day.$start_month.$start_year"); */
        /* } else if ($start_month == $end_month && $start_year == $end_year) { */
        /*     $this->renderer->cdata("$start_day – $end_day.$start_month.$start_year"); */
        /* } else if ($start_year == $end_year) { */
        /*     $this->renderer->cdata("$start_day.$start_month – $end_day.$end_month.$start_year"); */
        /* } else { */
        /*     $this->renderer->cdata("$start_day.$start_month.$start_year" */
        /*     ." – $end_day.$end_month.$end_year"); */
        /* } */
        $starttag = $this->readDateTime($row, "start", "d.m.y");
        $endtag = $this->readDateTime($row, "ende", "d.m.y");
        $this->renderer->cdata($starttag);
        if ($starttag != $endtag) {
            $this->renderer->cdata("–");
            if ($this->mode === 'xhtml') $this->renderer->doc .= "<wbr/>";
            //$this->renderer->linebreak();
            $this->renderer->cdata("$endtag");
        }
        $this->renderer->tablecell_close();

        $this->renderer->tablecell_open(1, "left");
        $datehint = $this->readRaw($row, "datehint");
        $startzeit = $this->readRaw($row, "startzeit");
        $endzeit = $this->readRaw($row, "endzeit");
        $this->renderer->cdata(" $datehint $startzeit");
        if (!empty($endzeit)) {
            $this->renderer->cdata("–");
            if ($this->mode === 'xhtml') $this->renderer->doc .= "<wbr/>";
            $this->renderer->cdata($endzeit);
        }
        $this->renderer->tablecell_close();

        $this->renderer->tablecell_open(1, "left");
        // col: Was? [Titel] aufklapp-symbol
        if ($this->mode === 'xhtml') $this->renderer->doc .= "<div class=\"station-termin-title station-termin-head\">";
        $titel = $this->readRaw($row, "titel");
        $this->renderer->cdata($titel);
        if ($this->mode === 'xhtml') $this->renderer->doc .= "</div>";

        if ($this->mode === 'xhtml') $this->renderer->doc .= "<div class=\"station-termin-body\">";

        //   [bild: rechtsbündig mit direct-link auf volle Größe]
        // @param string $src       media ID
        // @param string $title     descriptive text
        // @param string $align     left|center|right
        // @param int    $width     width of media in pixel
        // @param int    $height    height of media in pixel
        // @param string $cache     cache|recache|nocache
        // @param string $linking   linkonly|detail|nolink
        // @param bool   $return    return HTML instead of adding to $doc
        // @return void|string writes to doc attribute or returns html depends on $return
        if (!empty($this->readRaw($row, "bild"))) {
            $this->renderer->internalmedia(
                $this->readRaw($row, "bild"), null, "right", 300,
                null, null, "direct", false);
        }
        
        $this->renderer->listu_open("station-termin-info");
        if (!empty($this->readRaw($row, "thema"))) {
            $this->renderer->listitem_open(1);
            $this->renderer->cdata("Themengebiet: ".$this->readRaw($row, "thema"));
            $this->renderer->listitem_close();
        }
        if (!empty($this->readRaw($row, "alter"))) {
            $this->renderer->listitem_open(1);
            $this->renderer->cdata("Altersgruppe: ".$this->readRaw($row, "alter"));
            $this->renderer->listitem_close();
        }
        if (!empty($this->readRaw($row, "uebernacht"))) {
            $this->renderer->listitem_open(1);
            $this->renderer->cdata("Mit Übernachtung");
            $this->renderer->listitem_close();
        }
        if (!empty($this->readRaw($row, "anmeldung"))) {
            $this->renderer->listitem_open(1);
        // [anmeldung: Link "anmelden"]
            $this->renderer->cdata("Benötigt Anmeldung: ");
            $this->renderer->internallink(
                ":anmelden?@veranstaltung@=$starttag-$endtag $titel", "jetzt anmelden");
            $this->renderer->listitem_close();
        }
        if (!empty($this->readRaw($row, "seite"))) {
            $this->renderer->listitem_open(1);
            $this->renderer->cdata("Weitere Informationen: ");
            // @param string      $id         pageid
            // @param string|null $name       link name
            // @param string|null $search     adds search url param
            // @param bool        $returnonly whether to return html or write to doc attribute
            // @param string      $linktype   type to set use of headings
            // @return void|string writes to doc attribute or returns html depends on $returnonly
            $this->renderer->internallink($this->readRaw($row, "seite"));
            $this->renderer->listitem_close();
        }
        $this->renderer->listu_close();
       
        //   [beschreibung: als gerenderter Wiki-Text]        
        $row[$this->key["beschreibung"]]->render($this->renderer, $this->mode);

        if ($this->mode === 'xhtml') $this->renderer->doc .= "</div>";
        $this->renderer->tablecell_close();

        $this->renderer->tablerow_close();
    }
}
