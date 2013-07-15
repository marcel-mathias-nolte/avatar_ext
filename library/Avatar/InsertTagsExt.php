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


namespace KirstenRoschanski\Avatar;

/**
 * Class InsertTagsExt
 *
 * @copyright  Marcel Mathias Nolte 2013
 * @copyright  Kirsten Roschanski (C) 2013
 * @copyright  Tristan Lins (C) 2013
 * @author     Marcel Mathias Nolte <marcel.nolte@noltecomputer.de>
 * @author     Kirsten Roschanski <kirsten@kat-webdesign.de>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 */
class InsertTagsExt extends \System
{
	/**
	 * replace Inserttag
	 *
	 * @param string
	 *
	 * @return string
	 */
	public function replaceTags($strTag)
	{
		list($strTag, $strParams) = trimsplit('?', $strTag);
		$arrTag = trimsplit('::', $strTag);

		if ($arrTag[0] != 'avatar_be') {
			return false;
		}

		// get default settings
		$arrDims  = deserialize($GLOBALS['TL_CONFIG']['avatar_maxdims']);
		$strAlt   = $GLOBALS['TL_CONFIG']['avatar_default_alt'];
		$strTitle = $GLOBALS['TL_CONFIG']['avatar_default_title'];
		$strClass = $GLOBALS['TL_CONFIG']['avatar_default_class'];

		// parse query parameters
		$strParams = \String::decodeEntities($strParams);
		$strParams = str_replace('[&]', '&', $strParams);
		$arrParams = explode('&', $strParams);
		foreach ($arrParams as $strParam) {
			list($key, $value) = explode('=', $strParam);

			switch ($key) {
				case 'width':
					$arrDims[0] = $value;
					break;

				case 'height':
					$arrDims[1] = $value;
					break;

				case 'alt':
					$strAlt = specialchars($value);
					break;

				case 'title':
					$strTitle = specialchars($value);
					break;

				case 'class':
					$strClass = $value;
					break;

				case 'mode':
					$arrDims[2] = $value;
					break;
			}
		}

		// if no id given, return anonymous avatar
		if (!$arrTag[1]) {
			return $this->generateAnonymousAvatar($arrDims);
		}

		// search the user record
		$objUser = \UserModel::findByPk($arrTag[1]);

		// return anonymous avatar, if member not found
		if (!$objUser) {
			return $this->generateAnonymousAvatar($arrDims);
		}

		// get the avatar
		$strAvatar = $objUser->avatar;

		// parse the alt and title text
		$strAlt   = \String::parseSimpleTokens($strAlt, $objUser->row());
		$strTitle = \String::parseSimpleTokens($strTitle, $objUser->row());

		// avatar available and file exists
		if ($strAvatar &&
			($objFile = \FilesModel::findByPk($strAvatar)) &&
			file_exists(TL_ROOT . '/' . $objFile->path)
		) {
			$strAvatar = $objFile->path;
		}

		else if ($GLOBALS['TL_CONFIG']['avatar_fallback_image'] &&
			($objFile = \FilesModel::findByPk($GLOBALS['TL_CONFIG']['avatar_fallback_image'])) &&
			file_exists(TL_ROOT . '/' . $objFile->path)
		) {
			$strAvatar = $objFile->path;
		}

		// no avatar is set, but gender is available
		else if ($strAvatar == '' && \FrontendUser::getInstance()->gender != '') {
			$strAvatar = "system/modules/avatar/assets/" . \FrontendUser::getInstance()->gender . ".png";
		}

		// fallback to default avatar
		else {
			$strAvatar = 'system/modules/avatar/assets/male.png';
		}

		// resize if size is requested
		$this->resize($strAvatar, $arrDims);

		// generate the img tag
		return sprintf(
			'<img src="%s" width="%s" height="%s" alt="%s" title="%s" class="%s">',
			TL_FILES_URL . $strAvatar,
			$arrDims[0],
			$arrDims[1],
			$strAlt,
			$strTitle,
			$strClass
		);
	}

	protected function resize(&$strAvatar, &$arrDims)
	{
		if ($arrDims[0] || $arrDims[1]) {
			$strAvatar = \Image::get(
				$strAvatar,
				$arrDims[0],
				$arrDims[1],
				$arrDims[2]
			);

			// read the new size to keep proportion
			$objAvatar  = new \File($strAvatar);
			$arrDims[0] = $objAvatar->width;
			$arrDims[1] = $objAvatar->height;
		}
	}

	protected function generateAnonymousAvatar($arrDims)
	{
		if ($GLOBALS['TL_CONFIG']['avatar_fallback_image'] &&
			($objFile = \FilesModel::findByPk($GLOBALS['TL_CONFIG']['avatar_fallback_image']))
		) {
			$strAvatar = $objFile->path;
		}
		else {
			$strAvatar = 'system/modules/avatar/assets/male.png';
		}

		$this->resize($strAvatar, $arrDims);

		return sprintf(
			'<img src="%s" width="%s" height="%s" alt="%s" title="%s" class="%s">',
			$strAvatar,
			$arrDims[0],
			$arrDims[1],
			$GLOBALS['TL_CONFIG']['avatar_anonymous_alt'],
			$GLOBALS['TL_CONFIG']['avatar_anonymous_title'],
			$GLOBALS['TL_CONFIG']['avatar_anonymous_class']
		);
	}
}
