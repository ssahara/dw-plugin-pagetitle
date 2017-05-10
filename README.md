Page Title plugin for DokuWiki
==============================

Render title on the page
------------------------

Define and render a [pagename](https://www.dokuwiki.org/pagename), in other words a title of the wiki page.

```
  <title>
  **Page Title** plugin for DokuWiki
  </title>
```

The specified page title is rendered as `<h1 class="pagetitle">` element on the page.
You can use **bold**, *italic*, <sup>superscript</sup> and <sub>subscript</sub> text to show appropriate title on the page, for instance a chemical formula [Fe<sup>II</sup>(CN)<sub>6</sub>]<sup>4-</sup>. The formatted title is shown on the page, but it is converted to a plain text for the title of HTML document, like `FeII(CN)6]4-`. The page title plugin overwrites 'title' value of the [metadata](https://www.dokuwiki.org/devel:metadata) of the page to store the plain title text.

The page title becomes the title of HTML document in combination with your DokuWiki [site title](https://www.dokuwiki.org/config:title).
You may need to set [useheading](https://www.dokuwiki.org/config:useheading) option 
to "navigation" (other than "0") in order to show specified title in the browser title bar.


If you want to set a page title without showing itself on the page, you can instead use following syntax macro:

```
  ~~Title: **Page Title** plugin for DokuWiki ~~
```


Hierarchical breadcrumbs on the page
----------------------------------
(expermental)

```
<!--YOU_ARE_HERE-->
```

This syntax allow you to put a hierarchical breadcrumbs in the page. The place holder `<!--YOU_ARE_HERE-->` is replaced by the hierarchical breadcrumbs provided by this plugin,  which uses page id for a breadcrumbs name independently from [$conf('useheading')](https://www.dokuwiki.org/config:useheading) setting.


----
Licensed under the GNU Public License (GPL) version 2

More infomation is available:
  * https://www.dokuwiki.org/plugin:pagetitle

(c) 2014-2017 Satoshi Sahara \<sahara.satoshi@gmail.com>
