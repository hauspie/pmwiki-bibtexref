<?php

/* Copyright (C) 2004 Alexandre Courbot. <alexandrecourbot@linuxgames.com>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

See the COPYING file for more details. */


$BibtexPdfLink = "Attach:pdf.gif";
$BibtexUrlLink = "URL";
$BibtexBibLink = "BibTeX";

$BibtexGenerateDefaultUrlField = false;

$BibtexLang = array();

Markup("bibtexcite","inline","/\\{\\[(.*?),(.*?)\\]\\}/e","BibCite('$1', '$2')");

Markup("bibtexquery","fulltext","/\\bbibtexquery:\\[(.*?)\\]\\[(.*?)\\]\\[(.*?)\\]\\[(.*?)\\]/e","BibQuery('$1', '$2', '$3', '$4')");
Markup("bibtexsummary","fulltext","/\\bbibtexsummary:\\[(.*?),(.*?)\\]/e","BibSummary('$1', '$2')");
Markup("bibtexcomplete","fulltext","/\\bbibtexcomplete:\\[(.*?),(.*?)\\]/e","CompleteBibEntry('$1', '$2')");


SDV($HandleActions['bibentry'],'HandleBibEntry');

$BibEntries = array();

class BibtexEntry {
    var $values = array();
    var $bibfile;
    var $entryname;
    var $entrytype;

    function BibtexEntry($bibfile, $entryname)
    {
      $this->bibfile = $bibfile;
      $this->entryname = $entryname;
    }

    function evalCond($cond)
    {
      $toeval = "return (" . $cond . ");";
      $toeval = str_replace("&gt;", ">", $toeval);
      return eval($toeval);
    }

    function evalGet($get)
    {
       $get = str_replace("\\\"", "\"", $get);
       $get = str_replace("&gt;", ">", $get);
       eval('$res = ' . $get . ';');
      return $res;
    }

    function getAuthors()
    {
      $aut = $this->getFormat('AUTHOR');
      if ($aut == FALSE) return FALSE;
      $aut = explode(" and ", $aut);

      $ret = "";

      for ($i = 0; $i < count($aut); $i++)
        {
          $ret = $ret . $aut[$i];
          if ($i == count($aut) - 2) 
            $ret = $ret . " and ";
          else if ($i < count($aut) - 2) 
            $ret = $ret . ", ";
        }
      return $ret;
    }

    function getEditors()
    {
      $edi = $this->getFormat('EDITOR');
      if ($edi == FALSE) return FALSE;
      $edi = explode(" and ", $edi);

      $ret = "";

      for ($i = 0; $i < count($edi); $i++)
        {
      $ret = $ret . $edi[$i];
      if ($i == count($edi) - 2) $ret = $ret . " and ";
      else if ($i < count($edi) - 2) $ret = $ret . ", ";
        }
      return $ret;
    }
    
    function getName()
    {
      return $this->entryname;
    }

    function getTitle()
    {
      return $this->getFormat('TITLE');
    }

    function getAbstract()
    {
      return $this->get('ABSTRACT');
    }

    function getComment()
    {
      return $this->get('COMMENT');
    }

    function getPages()
    {
      $pages = $this->get('PAGES');
      if ($pages)
      {
          $found = strpos($pages, "--");
          if (found)
                return str_replace("--", "-", $pages);
          else
                return $pages;
      }
      return "";
    }

    function getPagesWithLabel()
    {
        $pages = $this->getPages();
        if ($pages)
        {
            if (is_numeric($pages[0]) && strpos($pages, "-")) 
                return "pages " . $pages;
            elseif (is_numeric($pages))
                return "page " . $pages;
        }
        return $pages;
    }
    
    function get($field)
    {
      $val = $this->values[$field];
      if ($val == FALSE) {
          $val = $this->values[strtolower($field)];
      }
      return trim($val);
    }


    function getFormat($field)
    {
      $ret = $this->get($field);
      if ($ret)
      {
        $ret = str_replace("{", "", $ret);
        $ret = str_replace("}", "", $ret);
      }
      return $ret;
    }

    function getCompleteEntryUrl()
    {
      global $DefaultTitle, $FarmD, $BibtexCompleteEntriesUrl;
      global $pagename;

      $Bibfile = $this->bibfile;
      $Entryname = $this->entryname;

      if ($Entryname != " ")
      {
        if (!$BibtexCompleteEntriesUrl)
            $BibtexCompleteEntriesUrl = FmtPageName('$PageUrl', $pagename) . '?action=bibentry&bibfile=$Bibfile&bibref=$Entryname';

        $RetUrl = preg_replace('/\$Bibfile/', "$Bibfile", $BibtexCompleteEntriesUrl);
        $RetUrl = preg_replace('/\$Entryname/', "$Entryname", $RetUrl);
      }
      return $RetUrl;
    }

    function getPreString($dourl = true)
    {
      // *****************************
      // Add LANG, AUTHOR, YEAR, TITLE
      // The golden rule is to always insert a ponctuation BEFORE a field not AFTER
      // because you're never sure there is going to be something after the field inserted.
      // *****************************
      global $pagename, $BibtexLang;
      $ret = "";

      $lang = $this->get("LANG");
      if ($lang && $BibtexLang[$lang])
      {
          $ret = $ret . $BibtexLang[$lang];
      }

      $author = $this->getAuthors();
      if ($author)
      {
          $ret = $ret . "'''" . $author . "'''";
      }

      $year = $this->get("YEAR");
      if ($year)
      {
          $ret = $ret . " (";
          $ret = $ret . $year . ") ";
      }

      if ($this->getTitle() != "")
      {
          if (false && $dourl && $this->entryname != " ")
              $ret = $ret . "''[[" . $this->getCompleteEntryUrl() . " | " . $this->getTitle() . "]]";
          else
              $ret = $ret . "''" . $this->getTitle();

          if (strlen($ret) > 2 && $ret[strlen($ret) - 1] != '?')
              $ret = $ret . ".";

          $ret = $ret . "''";
      }

      return $ret;
  }

  function getPostString($dourl = true)
  {
      // *****************************************
      // Add a point, NOTE, URL, PDF and BibTeX
      // The golden rule is to always insert a ponctuation BEFORE a field not AFTER
      // because you're never sure there is going to be something after the field inserted.
      // *****************************************

      global $ScriptUrl, $BibtexUrlLink, $BibtexBibLink, $pagename;

      $ret = "";

      $note = $this->get("NOTE");
      if ($note)
      {
        $ret = $ret . ". " . $note . ".";
      } else
        $ret = $ret . ".";

      // This field comes from JabRef
      $files = $this->get("FILE");
      if ($files)
      {
        // from http://stackoverflow.com/a/8519822
        $files = preg_split('~(?<!\\\)' . preg_quote(';', '~') . '~', $files);
        foreach ($files as $file)
        {
          $file = str_replace("\;", ";", $file);
          $file = preg_split('~(?<!\\\)' . preg_quote(':', '~') . '~', $file);
          if ($file[2] == "PDF")
          {
            global $BibtexPdfLink;
            $ret = $ret . " [[" . str_replace("\:", ":", $file[1]) . " | $BibtexPdfLink]]";
          }
        }
      }

      $pdf = $this->get("PDF");
      if ($pdf)
      {
        // BibtexPdfUrl is an url path where the pdf are stored
        // must be declared in config.php
        global $BibtexPdfUrl, $BibtexPdfLink;
        // TODO better url detection!
        if (strpos($pdf, "http") === FALSE || strpos($pdf, "ftp") === FALSE)
        {
          if (!$BibtexPdfUrl) 
            $BibtexPdfUrl = "Attach:";
        }
        else
          $BibtexPdfUrl = "";
        $ret = $ret . " [[$BibtexPdfUrl" . $pdf . " | $BibtexPdfLink]]";
      }

      $url = $this->get("URL");
      if ($url) 
      {
        $ret = $ret . " ([[" . $url . " | $BibtexUrlLink]])";
      }

      if ($dourl && $this->entryname != " ")
        $ret = $ret . " ([[" . $this->getCompleteEntryUrl() . "| $BibtexBibLink]])";

      return $ret;
    }

    function cite()
    {
      $ret = "([[" . $this->getCompleteEntryUrl() . " | " . $this->entryname . "]])";
      return $ret;
    }

    function getBibEntry()
    {
      global $BibtexSilentFields, $BibtexGenerateDefaultUrlField;

      $ret = $ret . "@@@" . $this->entrytype . " { " . $this->entryname . ",\\\\\n";

      while (list($key, $value)=each($this->values))
      {
        if ($BibtexSilentFields && in_array($key, $BibtexSilentFields)) continue;
        $ret = $ret . "&nbsp;&nbsp;&nbsp;&nbsp;" . $key . " = { " . $value . " },\\\\\n";
      }

      if ($BibtexGenerateDefaultUrlField && ($this->get("URL") == false)) $ret = $ret . "&nbsp;&nbsp;&nbsp;&nbsp;URL = { " . $this->getCompleteEntryUrl() . " },\\\\\n";
      $ret = $ret . "}@@\n";

      return $ret;
    }

    function getCompleteEntry()
    {
      $ret = "[[#" . $this->entryname . "]]\n!!!" . $this->entryname . "\n";
      $ret = $ret . '<div class="indent">' . $this->getSummary(false) . "</div>";
      $abstract = $this->getAbstract();
      if ($abstract)
      {
        $ret = $ret . "\n'''Abstract:'''\n" . '<div class="indent">' . $abstract . "</div>";
      }
      $comment = $this->getComment();
      if ($comment)
      {
        $ret = $ret . "\n'''Comment:'''\n" . '<div class="indent">' . $comment . "</div>";
      }
      $ret = $ret . "[[#" . $this->entryname . "Bib]]\n";
      $ret = $ret . "\n'''Bibtex entry:'''\n" . '<div class="indent">' . $this->getBibEntry() . "</div>";
      return $ret;
    }

    function getSolePageEntry()
    {
      $ret = "!" . $this->entryname . "\n";
      $ret = $ret . "\n!!!Summary\n";
      $ret = $ret . $this->getSummary(false) . "\n";
      $abstract = $this->getAbstract();
      if ($abstract)
      {
        $ret = $ret . "\n!!!Abstract\n" . $abstract . "\n";
      }
      $comment = $this->getComment();
      if ($comment)
      {
        $ret = $ret . "\n!!!Comment\n" . $comment . "\n";
      }
      $ret = $ret . "[[#" . $this->entryname . "Bib]]\n";
      $ret = $ret . "\n!!!Bibtex entry\n" . $this->getBibEntry() . "\n";
      return $ret;
    }
  }

  class PhdThesis extends BibtexEntry {
    function PhdThesis($bibfile, $entryname)
    {
      parent::BibtexEntry($bibfile, $entryname);
      $this->entrytype = "PHDTHESIS";
    }

    function getSummary($dourl = true)
    {
      $ret = parent::getPreString($dourl);
      $ret = $ret . " PhD thesis";
      $school = parent::get("INSTITUTION");
      if ($school)
      {
        $ret = $ret . ", ''" . $school . "''";
      }
      return $ret . parent::getPostString($dourl);
    }
  }

// *****************************
// MasterThesis
// *****************************
class MasterThesis extends BibtexEntry {
  function MasterThesis($bibfile, $entryname)
  {
    parent::BibtexEntry($bibfile, $entryname);
    $this->entrytype = "MASTERSTHESIS";
  }

  function getSummary($dourl = true)
  {
    $ret = parent::getPreString($dourl);

    $ret = $ret . " Master's thesis";
    $school = parent::get("INSTITUTION");
    if ($school)
    {
      $ret = $ret . ", ''" . $school . "''";
    }
    return $ret . parent::getPostString($dourl);
  }
}

// *****************************
// TechReport
// *****************************
class TechReport extends BibtexEntry {
  function TechReport($bibfile, $entryname)
  {
    parent::BibtexEntry($bibfile, $entryname);
    $this->entrytype = "TECHREPORT";
  }

  function getSummary($dourl = true)
  {
    $ret = parent::getPreString($dourl);
    $type = parent::get("TYPE");
    if ( $type )
       $ret = $ret . " $type";
    else
       $ret = $ret . " Technical report";
    
    $number = parent::get("NUMBER");
    if ($number)
       $ret = $ret . " $number";
    $institution = parent::get("INSTITUTION");
    if ($institution)
    {
      $ret = $ret . ", " . $institution;
    }
    return $ret . parent::getPostString($dourl);
  }
}

// *****************************
// Article
// *****************************
class Article extends BibtexEntry {
  function Article($bibfile, $entryname)
  {
    parent::BibtexEntry($bibfile, $entryname);
    $this->entrytype = "ARTICLE";
  }

  function getSummary($dourl = true)
  {
    $ret = parent::getPreString($dourl);
    $journal = parent::get("JOURNALTITLE");
    if ($journal)
    {
      $ret = $ret . " " . $journal;
      $volume = parent::get("VOLUME");
      if ($volume)
      {
        $ret = $ret . ", " . $volume;
        $number = parent::get("NUMBER");
        if ($number)
        {
          $ret = $ret . "(" . $number . ")";
        }
        $pages = parent::getPages();
        if ($pages)
        {
          $ret = $ret . ":" . $pages;
        }
        $publisher = parent::get("PUBLISHER");
        if ($publisher)
        {
          $ret = $ret . ". " . $publisher;
        }
      }
    }
    return $ret . ". " . parent::getPostString($dourl);
  }
}

// *****************************
// InProceedings
// *****************************
class InProceedings extends BibtexEntry
{
    function InProceedings($bibfile, $entryname)
    {
      parent::BibtexEntry($bibfile, $entryname);
      $this->entrytype = "INPROCEEDINGS";
    }

    function getSummary($dourl = true)
    {
        $ret = parent::getPreString($dourl);
        $booktitle = parent::get("BOOKTITLE");
        if ($booktitle)
        {
            $ret = $ret . " In " . $booktitle . ".";

            $address = parent::get("ADDRESS");
            if ($address)
            {
                if ($ret[strlen($ret) - 1] != '.')
                    $ret = $ret . ".";

                $ret = $ret . " " . $address;
            }
            
            $month = parent::get("MONTH");
            if ($month)
            {
                if ($ret[strlen($ret) - 1] != '.')
                    $ret = $ret . ",";

                $ret = $ret . " " . $month;
            }
            
            $editor = parent::getEditors();
            if ($editor)
            {
                if ($ret[strlen($ret)-1] != '.')
                    $ret = $ret . ".";

                $ret = $ret . " (" . $editor .", Eds.)";
            }

            $publisher = parent::get("PUBLISHER");
            if ($publisher)
            {
                if ($ret[strlen($ret)-1] != ')')
                    $ret = $ret . ".";
                $ret = $ret . " " . $publisher;
            }

            $pages = $this->getPagesWithLabel();
            if ($pages)
            {
                if ($ret[strlen($ret) - 1] != ')')
                    $ret = $ret . ",";
                elseif ($pages[0] == 'p')
                    $pages[0] = 'P';

                $ret = $ret . " " . $pages;
            }

            $organization = parent::get("ORGANIZATION");
            if ($organization)
            {
                if ($ret[strlen($ret) - 1] != ')')
                    $ret = $ret . ", ";
                $ret = $ret . ". " . $organization;
            }
        }

        return $ret . parent::getPostString($dourl);
    }

}

// *****************************
// InCollection
// *****************************
class InCollection extends BibtexEntry {
  function InCollection($bibfile, $entryname)
  {
    parent::BibtexEntry($bibfile, $entryname);
    $this->entrytype = "INCOLLECTION";
  }

  function getSummary($dourl = true)
  {
    $ret = parent::getPreString($dourl);
    $booktitle = parent::get("BOOKTITLE");
    if ($booktitle)
    {
        $ret = $ret . " In " . $booktitle . "";

        $editor = parent::getEditors();
        if ($editor)
        {
          $ret = $ret . " (" . $editor .", Eds.)";
        }

        $pages = $this->getPagesWithLabel();
        if ($pages)
            $ret = $ret . ", " . $pages . ".";

        $publisher = parent::get("PUBLISHER");
        if ($publisher)
        {
            if ($ret[strlen($ret)-1] != '.')
                $ret = $ret . ". ";
            $ret = $ret . " " . $publisher;
        }
    }
    return $ret . parent::getPostString($dourl);

  }

}

// *****************************
// Book
// *****************************
class Book extends BibtexEntry {
    function Book($bibfile, $entryname)
    {
      parent::BibtexEntry($bibfile, $entryname);
      $this->entrytype = "BOOK";
    }

    function getSummary($dourl = true)
    {

        $ret = $ret . parent::getPreString($dourl);
        
        $editor = $this->getEditors();
        if ($editor)
           $ret = $ret . " (" . $editor .", Eds.)";
        
        $publisher = parent::get("PUBLISHER");
        if ($publisher)
            $ret = $ret . " " . $publisher;

         $address = parent::get("ADDRESS");
         if ($address)
         {
             if ($ret && $ret[strlen($ret) - 1] != "." && $ret[strlen($ret) - 1] != "'")
                $ret = $ret . ",";
             $ret = $ret . " $address";
         }

        // Remove the point at the end of the string if only the title was provided
        if ($ret && $ret[strlen($ret) - 3] == '.')
            $ret = substr_replace($ret, "", strlen($ret) - 3, 1);
        
        $post = parent::getPostString($dourl);
        $ret = $ret . $post;
        
        return $ret;
    }
}

// *****************************
// InBook
// *****************************
class InBook extends BibtexEntry {
  function InBook($bibfile, $entryname)
  {
    parent::BibtexEntry($bibfile, $entryname);
    $this->entrytype = "INBOOK";
  }

    function getTitle()
    {
	return $this->getFormat('CHAPTER');
    }
    function getSummary($dourl = true)
    {
        $ret = $this->getPreString($dourl);
        $booktitle = parent::get("TITLE");
        if ($booktitle)
        {
            $ret = $ret . " In " . $booktitle . ".";
           
            $editor = parent::getEditors();
            if ($editor)
            {
                if ($ret[strlen($ret)-1] != '.')
                    $ret = $ret . ".";

                $ret = $ret . " (" . $editor .", Eds.)";
            }

            $address = parent::get("ADDRESS");
            if ($address)
            {
                if ($ret[strlen($ret) - 1] != '.')
                    $ret = $ret . ".";

                $ret = $ret . " " . $address;
            }
            
            $publisher = parent::get("PUBLISHER");
            if ($publisher)
            {
                if ($ret[strlen($ret)-1] != ',')
                    $ret = $ret . ",";
                $ret = $ret . " " . $publisher;
            }

            $pages = $this->getPagesWithLabel();
            if ($pages)
            {
                if ($ret[strlen($ret) - 1] != ')')
                    $ret = $ret . ",";
                elseif ($pages[0] == 'p')
                    $pages[0] = 'P';

                $ret = $ret . " " . $pages;
            }

            $organization = parent::get("ORGANIZATION");
            if ($organization)
            {
                if ($ret[strlen($ret) - 1] != ')')
                    $ret = $ret . ", ";
                $ret = $ret . ". " . $organization;
            }
        }

        return $ret . parent::getPostString($dourl);
    }

}

// *****************************
// Proceedings
// *****************************
class Proceedings extends BibtexEntry {
      function Proceedings($bibfile, $entryname)
      {
         parent::BibtexEntry($bibfile, $entryname);
         $this->entrytype = "PROCEEDINGS";
      }

      function getSummary($dourl = true)
      {
         $ret = parent::getPreString($dourl);
         $editor = parent::getEditors();
         if ($editor)
             $ret = $ret . " (" . $editor .", Eds.)";

         $volume = parent::get("VOLUME");
         if ($volume)
         {
            $ret = $ret . "volume " . $volume;
            $series = parent::get("SERIES");
            if ( $series != "" )
               $ret = $ret . " of ''$series''";
         }
         $address = parent::get("ADDRESS");
         if ($address)
            $ret = $ret . ", $address";
         $orga = parent::get("ORGANIZATION");
         if ($orga)
            $ret = $ret . ", $orga";
         $publisher = parent::get("PUBLISHER");
         if ($publisher)
            $ret = $ret . ", $publisher";
         $ret = $ret . parent::getPostString($dourl);
         return $ret;
      }
}

// *****************************
// Misc
// *****************************
class Misc extends BibtexEntry {
  function Misc($bibfile, $entryname)
  {
    parent::BibtexEntry($bibfile, $entryname);
    $this->entrytype = "MISC";
  }

  function getSummary($dourl = true)
  {
    $ret = parent::getPreString($dourl);
    
    $howpublished = parent::get("HOWPUBLISHED");
    if ($howpublished)
        $ret = $ret . " " . $howpublished;
        
    $ret = $ret . parent::getPostString($dourl);
    return $ret;
  }
}

function sortByField($a, $b)
{
  global $SortField;
  $f1 = $a->evalGet($SortField);
  $f2 = $b->evalGet($SortField);

  if ($f1 == $f2) return 0;

  return ($f1 < $f2) ? -1 : 1;
}

function BibQuery($files, $cond, $sort, $max)
{
    global $BibEntries, $SortField;

    $ret = '';

    $files = trim($files);
    $cond = trim($cond);
    $sort = trim($sort);

    if ($sort[0] == '!')
    {
        $reverse = true; $sort = substr($sort, 1);
    }
    else $reverse = false;

    if ($cond == '') $cond = 'true';

    if (!$BibEntries[$files])
    {
        if (!ParseBibFile($files))
            return "%red%Invalid BibTex File!";
    }


    $res = array();
    $bibselectedentries = $BibEntries[$files];
    while (list($key, $value)=each($bibselectedentries))
    {
       
        if ($value->evalCond($cond))
            $res[] = $value;
    }

    if ($sort != '')
    {
        $SortField = $sort;
        usort($res, "sortByField");
    }

    if ($reverse)
        $res = array_reverse($res);

    if ($max != '')
        $res = array_slice($res, 0, (int) $max);

    while (list($key, $value)=each($res))
    {
        $ret .= "#" . $value->getSummary() . "\n";
    }

    return $ret;
}

function HandleBibEntry($pagename)
{
  global $PageStartFmt, $PageEndFmt, $PageHeaderFmt, $ScriptUrl, $bibentry, $bibfile, $bibref;
  $bibfile = $_GET['bibfile'];
  $bibref = $_GET['bibref'];
  SDV($ScriptUrl, FmtPageName('$PageUrl', $pagename));

  $bibentry = GetEntry($bibfile, $bibref);

  $page = array('timefmt'=>@$GLOBALS['CurrentTime'],
		'author'=>@$GLOBALS['Author']);

  $PageHeaderFmt = "";
  SDV($HandleBibtexFmt,array(&$PageStartFmt,
    'function:PrintCompleteEntry',&$PageEndFmt));
  PrintFmt($pagename,$HandleBibtexFmt);
}

function PrintCompleteEntry()
{
  global $bibentry, $bibfile, $bibref, $pagename;
  if ($bibentry == false) echo MarkupToHTML($pagename, "%red%Invalid BibTex Entry: [" . $bibfile . ", " . $bibref . "]!");
  else
    {
      echo MarkupToHTML($pagename, $bibentry->getSolePageEntry());
    }
}

function GetEntry($bib, $ref)
{
    global $BibEntries;
    $ref = trim($ref);
    $bib = trim($bib);
    $bibtable = $BibEntries[$bib];
    if ($bibtable == false)
    {
      ParseBibFile($bib);
      $bibtable = $BibEntries[$bib];
    }
    
    reset($bibtable);

    while (list($key, $value)=each($bibtable))
    {
        if ($value->getName() == $ref)
        {
            $bibref = $value;
            break;
        }
    }

    if ($bibref == false)
      return false;
    return $bibref;
}

function BibCite($bib, $ref)
{
  $entry = GetEntry($bib, $ref);
  if ($entry == false) return "%red%Invalid BibTex Entry!";
  return $entry->cite();
}

function CompleteBibEntry($bib, $ref)
{
  $entry = GetEntry($bib, $ref);
  if ($entry == false) return "%red%Invalid BibTex Entry!";
  return $entry->getCompleteEntry();
}

function BibSummary($bib, $ref)
{
  $entry = GetEntry($bib, $ref);
  if ($entry == false) return "%red%Invalid BibTex Entry!";
  return $entry->getSummary();
}

function ParseEntries($fname, $entries)
{
   global $BibEntries;
   $nb_entries = count($entries[0]);

   $bibfileentry = array();
   for ($i = 0 ; $i < $nb_entries ; ++$i)
   {
      $entrytype = strtoupper($entries[1][$i]);
      
      $entryname = $entries[2][$i];

      //if ($i < 5)
      //  print "<font color=#FF0000>Allo nb_entries=$nb_entries entryname=$entryname</font><br>\n";
        
      if ($entrytype == "ARTICLE") $entry = new Article($fname, $entryname);
      else if ($entrytype == "INPROCEEDINGS") $entry = new InProceedings($fname, $entryname);
      else if ($entrytype == "PHDTHESIS") $entry = new PhdThesis($fname, $entryname);
      else if ($entrytype == "MASTERSTHESIS") $entry = new MasterThesis($fname, $entryname);
      else if ($entrytype == "INCOLLECTION") $entry = new InCollection($fname, $entryname);
      else if ($entrytype == "BOOK") $entry = new Book($fname, $entryname);
      else if ($entrytype == "INBOOK") $entry = new InBook($fname, $entryname);
      else if ($entrytype == "TECHREPORT") $entry = new TechReport($fname, $entryname);
      else if ($entrytype == "PROCEEDINGS") $entry = new Proceedings($fname, $entryname);
      else $entry = new Misc($fname, $entryname);

      // match all keys
      preg_match_all("/(\w+)\s*=\s*([^¶]+)¶?/", $entries[3][$i], $all_keys);

      
      for ($j = 0 ; $j < count($all_keys[0]) ; $j++)
      {
        $key = strtoupper($all_keys[1][$j]);
        $value = $all_keys[2][$j];
        // Remove the leading and ending braces or quotes if they exist.
        $value = preg_replace('/^\s*{(.*)}\s*$/', '\1', $value);
        // TODO: only run this regexp if the former didn't match
        $value = preg_replace('/^\s*"(.*)"\s*$/', '\1', $value);

        $entry->values[$key] = $value;
      }
      
//$val = "<font color=#FF0000>char = " . $entry->values["AUTHOR"][2]. $entry->values["AUTHOR"][3]. $entry->values["AUTHOR"][4] . "</font><br><br>\n";
//print $val;
      $bibfileentry[] = $entry;
   }


   $BibEntries[$fname] = $bibfileentry;
}


function ParseBib($bib_file, $bib_file_string)
{
// first split the bib file into several part
// first let's do an ugly trick to replace the first { and the last } of each bib entry by another special char (to help with regexp)
   $count=0;
   for ($i = 0 ; $i < strlen($bib_file_string) ; $i++)
   {
      if ($bib_file_string[$i] == '{')
      {
         if ($count==0)
            $bib_file_string[$i] = '¤';
         $count++;
      }
      else if ($bib_file_string[$i] == '}')
      {
         $count--;
         if ($count==0)
            $bib_file_string[$i] = '¤';
      }
      else if ($bib_file_string[$i] == ',' && $count == 1)
      {
        $bib_file_string[$i] = '¶';
      }
      else if ($bib_file_string[$i] == "\r" && $count == 1)
        $bib_file_string[$i] = '¶';
   }

   $bib_file_string = preg_replace("/¶¶/", "¶", $bib_file_string);
   
   $nb_bibentry = preg_match_all("/@(\w+)\s*¤\s*([^¶]*)¶([^¤]*)¤/", $bib_file_string, $matches);

   ParseEntries($bib_file, $matches);
}

function ParseBibFile($bib_file)
{
    global $BibtexBibDir, $pagename;


    $wikibib_file = MakePageName($pagename, $bib_file);

    if (PageExists($wikibib_file))
    {
        $bib_file_string = ReadPage($wikibib_file, READPAGE_CURRENT);
        $bib_file_string = $bib_file_string['text'];

        $bib_file_string = preg_replace("/\n/", "\r", $bib_file_string); // %0a

        ParseBib($bib_file, $bib_file_string);
        return true;
    }
    else
    {
        if (!$BibtexBibDir)
            $BibtexBibDir = FmtPageName('$UploadDir$UploadPrefixFmt', $pagename);
    
        if (file_exists($BibtexBibDir . $bib_file))
        {
            $f = fopen($BibtexBibDir . $bib_file, "r");
            $bib_file_string = "";

            if ($f)
            {
                while (!feof($f))
                {
                    $bib_file_string = $bib_file_string . fgets($f, 1024);
                }

                $bib_file_string = preg_replace("/\n/", "", $bib_file_string);

                ParseBib($bib_file, $bib_file_string);

                return true;
            }
            return false;
        }
    }
}

$UploadExts['bib'] = 'text/plain';

?>
