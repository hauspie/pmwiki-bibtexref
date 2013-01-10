pmwiki-bibtexref
================

This little script allows you to display your bibtex bibliography into PmWiki
in a convenient way and to put references to it. It introduces new markups and
works directly over .bib files.  Comments, suggestions should go to
Michael.Hauspie@lifl.fr.  You can see an example usage of this script at
http://www.lifl.fr/2XS/Team/Publications. It demonstrates the query feature.


Installation
============

Get the source package and untar it inside your cookbook/ directory. You
should have the following hierarchy:

  cookbook/bibtexref/

Then add

  include 'cookbook/bibtexref/bibtexref2.php';

to your config.php.


Alternatively, you could use one of the newer, modified versions posted in the
Comments section below. It is not required to install the script in its own
"bibtexref" directory.

You should also set the $BibtexBibDir variable to the location of your bibtex
files. By default, the location would be the upload directory for the current
page, which is probably not what you want.

Also, you should upload a pdf.gif image file for displaying links to pdfs.

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

Contributors
============

Alexandre Courbot (http://www.gnurou.org/)
Michaël Hauspie (http://www.lifl.fr/~hauspie)
Yann Hodique (http://www.hodique.info/)
Romain Rouvoy (http://www.lifl.fr/~rouvoy)

Copyright
=========
This software is distributed under the GNU General Public License.
