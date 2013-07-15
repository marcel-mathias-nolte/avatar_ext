<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2012 Leo Feyer
 * 
 * @package   Avatar Extension
 * @author    Marcel Mathias Nolte
 * @copyright Marcel Mathias Nolte 2013
 * @credits   Kirsten Roschanski 2013
 * @credits   Tristan Lins <http://bit3.de> 2013
 * @website   https://www.noltecomputer.com
 * @license   <marcel.nolte@noltecomputer.de> wrote this file. As long as you retain this notice you
 *            can do whatever you want with this stuff. If we meet some day, and you think this stuff 
 *            is worth it, you can buy me a beer in return. Meanwhile you can provide a link to my
 *            homepage, if you want, or send me a postcard. Be creative! Marcel Mathias Nolte
 */
 
/**
 * Register hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('Avatar\InsertTagsExt', 'replaceTags');

?>