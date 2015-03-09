pmwiki-bibtexref
================

This little script allows you to display your bibtex bibliography into PmWiki
in a convenient way and to put references to it. It introduces new markups and
works directly over .bib files.

You can see an example usage of a previous version of this script at
http://www.lifl.fr/2XS/Team/Publications. It demonstrates the query feature.

Another example is http://www.irit.fr/~Victor.Noel/Main/Publications.
It uses the last version of this script.


Installation
============

Get the source package and untar it inside your cookbook/ directory. You
should have the following hierarchy:

  cookbook/bibtexref/

Then add

  include 'cookbook/bibtexref/bibtexref3.php';

to your config.php.

You can set the $BibtexBibDir variable to the location of your bibtex
files (in the filesystem). By default, the location would be the upload directory for the current
page, which is probably not what you want.

You can set the $BibtexPdfUrl variable to the location of your pdf files (as an url).

Also, you should upload a pdf.gif image file for displaying links to pdfs.

Bibtex Entries
==============

The notation followed is the one from biblatex.

A Pdf field in a bibtex entry must either contain an absolute url (which will be use as is) or
a filename (which should either be accessible via $BibtexPdfUrl if defined or attached to the current page).

Alternatively, the File field notation from JabRef can be used but only for urls to pdfs.


New actions
===========

This script introduces a new PmWiki action: bibentry, which is used to display
a bibtex entry on one page. It requires two additional parameters, bibfile (the
file name to take the bibtex entry from) and bibref (the reference of the entry
to display).  PmWiki tags

To make bibliographical references, you must first upload a few .bib files,
either using PmWiki upload facility, or by copying them to
uploads/Group/. Bibliography files are only usable within the group they are
uploaded from.

Citations are made with the tags `{[...]}` with the form:
`{[bibfile.bib,reference]}`. They will expand to a clickable `[reference]` that
will automatically direct you to the whole bibtex entry.

You can also cite the entry summary using

    bibtexsummary:[bibfile.bib,reference].

Or you can quote the complete bibtex entry with

    bibtexcomplete:[bibfile.bib,reference].

You can also make bibtex queries, using the following syntax:

    bibtexquery:[bibfile.bib],[select rule][sort rule][limit rule]

"select rule", "sort rule" and "limit rule" are directly PHP code. An example
is much better than any explanation here:
  
    bibtexquery:[bibfile.bib][$this->get('YEAR') == '2004'][!$this->get('PUBDATE')][5]

gets the first five articles from 2004, sorted on publication date.
  
    bibtexquery:[bibfile.bib][strpos($this->get('AUTHOR'),'John Doe')!==FALSE][!$this->get('PUBDATE')][100]  

Get the articles with 'John Doe' in the author list. A simular construct can
also be used to search for words in the titel, abstract, etc. The '!==FALSE'
construct is necessary because 'strpos' will return 0 when the field begins
with your keyword.  

You can also have the author list first, and highlight a particular NAME in
bold, which is useful to show where NAME appears in a publications

    bibtexsummaryauthorbold:[bibfile.bib,reference,NAME].


Contributors
============

* Alexandre Courbot (http://www.gnurou.org/)
* Michaël Hauspie (http://www.lifl.fr/~hauspie)
* Yann Hodique (http://www.hodique.info/)
* Romain Rouvoy (http://www.lifl.fr/~rouvoy)
* Victor Noël (https://github.com/victornoel/)

Copyright
=========
This software is distributed under the GNU General Public License.
