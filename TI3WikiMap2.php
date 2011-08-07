<?php

/*
	Features to implement:
	*Text aliasing within ti3 blocks
	*Group settings for setting color, glow and race according to just one player setting
	*Scaling of units (make smaller units on tiles)
	*Give map ability to render a specific page revision of data page (map data page contains a copy of map so it will be in history)
	*Prevent map tag recursion, so that a copy of map can be shown in the data page
	*(Shrink the clock ring down a bit, too many pieces stick out the edges)
	*Create radius, percent distance settings for adjusting position (have clock positions calculated based on this)
	*Automatic planet name assignment according to order
	Automatic ship placement?
	*Preview edit works
	Add background box command
	
	Bugs:
	Right quantiy box at 25% is messed up
	Auto- template linkage is not always occuring correctly for map data (perhaps because map data page is not a template?)
	
	Mozilla:
	At 25% zoom, quantity boxes have a one pixel veritcal break between the left and whatever is next
	Quantity labels randomly default back to CSS color setting for anchor
	
	IE:
	Tile labels don't display correctly at all
	At 25%, 50% zooms, quantity boxes have a one pixel veritcal break between the left and whatever is next
	
*/
$wgExtensionFunctions[] = 'wfTI3WikiMap2Functions';

$wgExtensionCredits['parserhook'][] = array(
'name' => 'TI3WikiMap2Functions v0.1',
);

$TI3WM_indexURL = "http://www.ti3wiki.org/index.php?title=";

$TI3WM_aliases = array();
$TI3WM_savedSettings = array();

$TI3WM_hooks = array(
	'tile' => 'TI3WM_Tile'
	,'unit' => 'TI3WM_Unit'
	,'planet' => 'TI3WM_Planet'
	,'ship' => 'TI3WM_Ship'
	,'carrier' => 'TI3WM_Carrier'
	,'destroyer' => 'TI3WM_Destroyer'
	,'cruiser' => 'TI3WM_Cruiser'
	,'dreadnaught' => 'TI3WM_Dreadnaught'
	,'warsun' => 'TI3WM_Warsun'
	,'leader' => 'TI3WM_Leader'
	,'cc' => 'TI3WM_CC'
	,'token' => 'TI3WM_Token'
	,'flag' => 'TI3WM_Flag'

	,'image' => 'TI3WM_Image'
	,'redirect' => 'TI3WM_ImageRedirect'

	,'ti3' => 'TI3WM_Parse'
	,'map' => 'TI3WM_Map'
	,'showsingletile' => 'TI3WM_ShowSingleTile'
);

function wfTI3WikiMap2Functions() {
    global $wgParser, $TI3WM_hooks, $TI3WM_tags;

	$TI3WM_tags = array_keys($TI3WM_hooks);
	$TI3WM_tags = array_merge($TI3WM_tags, array(	'alias'
													,'set'
													,'get'
													,'global'
													,'a'
													,'save'
													,'recall'
												)
							);
	
    $wgParser->setHook('ti3','TI3WM_TagHook_ti3');
	$wgParser->setHook('tile','TI3WM_TagHook_tile');
	$wgParser->setHook('unit','TI3WM_TagHook_unit');
	$wgParser->setHook('planet','TI3WM_TagHook_planet');
	$wgParser->setHook('ship','TI3WM_TagHook_ship');
	$wgParser->setHook('carrier','TI3WM_TagHook_carrier');
	$wgParser->setHook('destroyer','TI3WM_TagHook_destroyer');
	$wgParser->setHook('cruiser','TI3WM_TagHook_cruiser');
	$wgParser->setHook('dreadnaught','TI3WM_TagHook_dreadnaught');
	$wgParser->setHook('warsun','TI3WM_TagHook_warsun');
	$wgParser->setHook('leader','TI3WM_TagHook_leader');
	$wgParser->setHook('cc','TI3WM_TagHook_cc');
	$wgParser->setHook('token','TI3WM_TagHook_token');
	$wgParser->setHook('flag','TI3WM_TagHook_flag');
}

function TI3WM_TagHook_ti3($content, $args, &$parser) { return TI3WM_TagHook('ti3', $content, $args, &$parser); };
function TI3WM_TagHook_tile($content, $args, &$parser) { return TI3WM_TagHook('tile', $content, $args, &$parser); };
function TI3WM_TagHook_unit($content, $args, &$parser) { return TI3WM_TagHook('unit', $content, $args, &$parser); };
function TI3WM_TagHook_planet($content, $args, &$parser) { return TI3WM_TagHook('planet', $content, $args, &$parser); };
function TI3WM_TagHook_ship($content, $args, &$parser) { return TI3WM_TagHook('ship', $content, $args, &$parser); };
function TI3WM_TagHook_carrier($content, $args, &$parser) { return TI3WM_TagHook('carrier', $content, $args, &$parser); };
function TI3WM_TagHook_destroyer($content, $args, &$parser) { return TI3WM_TagHook('destroyer', $content, $args, &$parser); };
function TI3WM_TagHook_cruiser($content, $args, &$parser) { return TI3WM_TagHook('cruiser', $content, $args, &$parser); };
function TI3WM_TagHook_dreadnaught($content, $args, &$parser) { return TI3WM_TagHook('dreadnaught', $content, $args, &$parser); };
function TI3WM_TagHook_warsun($content, $args, &$parser) { return TI3WM_TagHook('warsun', $content, $args, &$parser); };
function TI3WM_TagHook_leader($content, $args, &$parser) { return TI3WM_TagHook('leader', $content, $args, &$parser); };
function TI3WM_TagHook_cc($content, $args, &$parser) { return TI3WM_TagHook('cc', $content, $args, &$parser); };
function TI3WM_TagHook_token($content, $args, &$parser) { return TI3WM_TagHook('token', $content, $args, &$parser); };
function TI3WM_TagHook_flag($content, $args, &$parser) { return TI3WM_TagHook('flag', $content, $args, &$parser); };
function TI3WM_TagHook_showsingletile($content, $args, &$parser) { return TI3WM_TagHook('showsingletile', $content, $args, &$parser); };

function TI3WM_GetImageScale($Scale)
{
	if($Scale > 0.75)
		return '1';
	if($Scale > 0.5)
		return '0.75';
	else
	if($Scale > 0.25)
		return '0.5';
	return '0.25';		
}

/*
nice idea but doesn't work since with wiki parser tags, parameters lose case sensitivity AND
things like Abyz-Fria get parsed as abyz

function TI3WM_SetDefaultArg(&$args, $defaultArg)
}*/

function TI3WM_GetImageURL($URL, $ImgScale)
{
	global $TI3WM_redirects;
	
	if(strpos($URL, 'http://') === false)
		$URL = "http://www.ti3wiki.org/wikimap/${ImgScale}/$URL";
	else
		$URL = str_replace('$scale',$ImgScale, $URL);
	
	if(!isset($TI3WM_redirects[$URL]))
		return $URL;
	return $TI3WM_redirects[$URL];
}

function TI3WM_StoreImageSizeCache()
{
	global $TI3WM_imageSizeCache;
	
//	apc_store('TI3WM_imageSizeCache', $TI3WM_imageSizeCache, 3600);
}

function TI3WM_GetImageInfo($URL, $Scale)
{
	global $TI3WM_imageSizeCache, $TI3WM_imageSizeCache_Loaded;

	if(!$TI3WM_imageSizeCache_Loaded)
	{
//		$TI3WM_imageSizeCache = apc_fetch('TI3WM_imageSizeCache');
		
		if($TI3WM_imageSizeCache === false)
			$TI3WM_imageSizeCache = array();
		
		register_shutdown_function('TI3WM_StoreImageSizeCache');
		
		$TI3WM_imageSizeCache_Loaded = true;
	}
	
	$ImgScale = TI3WM_GetImageScale($Scale);
	
	$URL = TI3WM_GetImageURL($URL, $ImgScale);
	$URL_1 = str_replace(array('0.75','0.5','0.25','http://www.ti3wiki.org/'),array('1','1','1','./'), $URL);

	if(!isset($TI3WM_imageSizeCache[$URL_1]))
		$TI3WM_imageSizeCache[$URL_1] = @getimagesize($URL_1);
	
	$a = $TI3WM_imageSizeCache[$URL_1];
	return array($URL, $ImgScale, $a[0], $a[1]);
}

function TI3WM_TagHook( $tag, $content, $args, &$parser)
{
	global $TI3WM_hooks, $TI3WM_defaultArgs, $TI3WM_mapInProgress, $TI3WM_savedSettings;
	global $TI3WM_globalsLoaded, $wgRequest, $TI3WM_mapArgs;
	
	if(	$tag != 'map' 
		&& $tag != 'showsingletile' 
		&& $wgRequest->getVal('action') == 'submit'
		&& !$TI3WM_globalsLoaded
	  )
	{
		// For previews when editing (a section), ensure globals are loaded, but that the output parameter is overriden to pic
		$Page = $parser->getTitle()->getPartialURL();
		$TI3WM_globalsLoaded = true;
		TI3WM_ParsePage($Page, &$parser);
		$TI3WM_defaultArgs['output'] = 'pic';
	}
			
	$margs = array_merge($TI3WM_defaultArgs, $args);
	
	if($margs['scale'] < 0.05) $margs['scale'] = 0.05;
	if($margs['scale'] > 2.0) $margs['scale'] = 2.0;	

	if($TI3WM_mapInProgress)
	{
		foreach($TI3WM_mapArgs as $k=>$v)
			$margs[$k] = $v;
	}
	
	$Output = $margs['output'];
	
	if($Output == 'html')
		$endl = "\n";

	if(isset($margs['recall']))
	{
		$Recall = $margs['recall'];
		if(is_array($TI3WM_savedSettings[$Recall]))
			$margs = array_merge($margs, $TI3WM_savedSettings[$Recall]);
		unset($margs['recall']);
	}
	
	if(isset($TI3WM_hooks[$tag]))
	{
		$retv = call_user_func($TI3WM_hooks[$tag], $content, $margs, &$parser);

		if($TI3WM_mapInProgress)
			return $retv;
			
		if(strlen($retv) > 0 && !$TI3WM_mapInProgress)
		{
/*			if($tag == 'tile')
				$retv = "<div style=\"position: relative;\">$endl$retv</div>$endl";
			else*/
				$retv = "<div style=\"display: inline;\">$end$retv</div>$endl";
		}
		
		if($Output == 'txt')
		{
			$text = "<$tag";
			foreach($args as $k=>$v)
			{
				if(strpos($v, ' ') !== false)
					$text .= " $k=\"$v\"";
				else $text .= " $k=$v";
			}
			
			if($content)
				$text .= ">$content</$tag>";
			else $text .= '/></pre>';	
			
			$text = '<pre>' . htmlentities($text) . '</pre>';
			return $text;
		}
		else
		if($Output == 'html')
			return '<pre>' . htmlentities($retv) . '</pre>';
			
		return $retv;
	}
	return '';
}

function TI3WM_Parse($content, $args, &$parser, $parsingGlobals = false)
{
	global $TI3WM_tags, $TI3WM_defaultArgs, $TI3WM_hooks, $TI3WM_mapInProgress;
	global $TI3WM_aliases, $TI3WM_savedSettings, $TI3WM_tagDefaultArg, $TI3WM_globalsLoaded;
	global $TI3WM_indexURL, $TI3WM_mapArgs;

	Parser::extractTagsAndParams(	array_merge($TI3WM_tags, array_keys($TI3WM_aliases))
								   ,$content
								   ,$matches 
								);	
	
	foreach($matches as $i)
	{
		$mtag = $i[0];
		$mcontent = $i[1];
		$margs = array_merge($args, $i[2]);

		foreach($TI3WM_aliases as $alias=>$translation)
		{
			if($alias == $mtag)
				$mtag = $translation;
			
			foreach($margs as $k=>$v)
			{
				if($v == $alias)
					$margs[$k] = $translation;
					
				if($k == $alias)
				{
					$margs[$translation] = $margs[$k];
					unset($margs[$k]);
				}
				
			}
			
		}
		
		if(isset($margs['recall']))
		{
			$Recall = $margs['recall'];
			if(is_array($TI3WM_savedSettings[$Recall]))
				$margs = array_merge($margs, $TI3WM_savedSettings[$Recall]);
			unset($margs['recall']);
		}
		
		if($margs['scale'] < 0.05) $margs['scale'] = 0.05;
		if($margs['scale'] > 2.0) $margs['scale'] = 2.0;	
	
		if($TI3WM_mapInProgress)
		{
			foreach($TI3WM_mapArgs as $k=>$v)
				$margs[$k] = $v;
		}
		
		switch($mtag)
		{
			case 'a' :
				$inner = TI3WM_Parse($mcontent, $margs, &$parser);
				$href = $margs['href'];
				if(strpos($href, 'http://')===false)
					$href = $TI3WM_indexURL . $href;
			
				$retv .= "<a href=\"$href\">$inner</a>";
				break;
			case 'global' :
				$retv .= TI3WM_Parse($mcontent, array_merge($TI3WM_defaultArgs, $i[2]), &$parser, true);
				$TI3WM_globalsLoaded = true;
				break;
				
			case 'alias' :
				$TI3WM_aliases = array_merge($TI3WM_aliases, $i[2]);
				break;
			case 'set' : 
				if(strlen($mcontent) == 0)
				{
					$args = $margs;
				}
				else $retv .= TI3WM_Parse($mcontent, $margs, &$parser);
				break;
			case 'get' :
				if($parsingGlobals)
				{
					$retv .= 'global{';
					foreach($TI3WM_defaultArgs as $k => $v)
						$retv .= " $k=$v";
					$retv .= '}<br/>';
				}
				else
				{
					$retv .= 'args{';
					foreach($args as $k => $v)
						$retv .= " $k=$v";
					$retv .= '}<br/>';
				}
				break;
			case 'save' :
				$name = $margs['name'];
				$TI3WM_savedSettings[$name] = $i[2];
				break;
			default :
				if(($mtag == 'map' || $mtag == 'showsingletile') && $TI3WM_mapInProgress)
					continue;
						
				if(isset($TI3WM_hooks[$mtag]))
					$retv .= call_user_func($TI3WM_hooks[$mtag], $mcontent, $margs, &$parser);
		}
	}

	if($parsingGlobals)
	{
		$TI3WM_defaultArgs = $args;
	}
		
	return $retv;
}
	
$TI3WM_defaultArgs = array ( 
//	'type' => 'GF'
	'color' => 'Grey'
//	,'damaged' => ''
//	,'race' => ''
	,'quantity' => '1'
//	,'planet' => ''
	,'where' => ''
	,'x' => '0'
	,'y' => '0'
//	,'x' => '216'
//	,'y' => '188'
//	,'z' => '1'
	,'scale' => '1'
	,'piecescale' => '1'
	,'where' => 'notset'
	,'radius' => '0.67'
	,'angle' => 'notset'
	,'center' => '1'
	,'output' => 'pic'
	
	,'spacedock' => '0'
	,'fighters' => '0'
	,'gf' => '0'
	,'st' => '0'
	,'pds' => '0'

	,'tile' => 'Empty'
//	,'label' => ''
	,'format' => 'gif'
	
	,'flag' => 'Wavy'
	,'unit' => 'gf'
	,'token' => 'Space_Mines'
	,'leader' => 'Admiral'
//	,'race' => 'notset'
	
//	,'ImageLink' => ''
	,'labelcolor' => 'white'
	,'labelsize' => 40
	,'width' => '7'
	,'height' => '7'
//	,'savemap' => '0'
	,'shownumbers' => '1'
	,'void' => 'Transparent'
//	,'scalelinks' => '1'
	,'tileexistsxylinks' => '1'
	,'tilemissingxylinks' => '1'
	,'tileimagelinks' => '1'
//	,'helpincludelinks' => '1'
);

$TI3WM_clockPos = array(
	array(0,0)
	,array(304, 60)
	,array(368, 119)
	,array(391, 188)
	,array(361, 263)
	,array(297, 317)
	,array(215, 336)
	,array(133, 315)
	,array(64, 262)
	,array(35, 186)
	,array(68, 117)
	,array(132, 60)
	,array(212, 42)
	,'center' => array(216, 188)
);

$TI3WM_planetCodes = array(
	'HS-Hacan' => array('HS3xP1','HS3xP2','HS3xP3')
	,'Arretze' => 'HS3xP1'
	,'Kamdorn' => 'HS3xP2'
	,'Hercant' => 'HS3xP3'
	
	,'HS-Letnev' => array('HS2xP1','HS2xP2')
	,'Arc Prime' => 'HS2xP1'
	,'Arc_Prime' => 'HS2xP1'
	,'Wren Terra' => 'HS2xP2'
	,'Wren_Terra' => 'HS2xP2'
	
	,'HS-Yssaril' => array('HS2xP1','HS2xP2')
	,'Retillion' => 'HS2xP1'
	,'Shalloq' => 'HS2xP2'

	,'HS-Sol' => 'HSP1'
	,'Jord' => 'HSP1'
	
	,'HS-Naalu' => array('HS2xP1','HS2xP2')
	,'Maaluuk' => 'HS2xP1'
	,'Druaa' => 'HS2xP2'
	
	,'HS-L1z1x' => 'HSP1'
	,'[0,0,0]' => 'HSP1'
	,'000' => 'HSP1'
	
	,'HS-Jolnar' => array('HS2xP1','HS2xP2')
	,'Nar' => 'HS2xP1'
	,'Jol' => 'HS2xP2'
	
	,'HS-Norr' => array('HS2xP1','HS2xP2')
	,'Tren\'lak' => 'HS2xP1'
	,'Trenlak' => 'HS2xP1'
	,'Quinarra' => 'HS2xP2'
	
	,'HS-Xxcha' => array('HS2xP1','HS2xP2')
	,'Archon Ren' => 'HS2xP1'
	,'Archon_Ren' => 'HS2xP1'
	,'Archon Tau' => 'HS2xP2'
	,'Archon_Tau' => 'HS2xP2'
	
	,'HS-Mentak' => 'HSP1'	
	,'Moll Primus' => 'HSP1'
	,'Moll_Primus' => 'HSP1'
	
	,'Mecatol Rex' => 'P1'
	,'Mecatol_Rex' => 'P1'
	
	,'New_Albion-Starpoint' => array('2xP1','2xP2')
	,'New Albion' => '2xP1'
	,'New_Albion' => '2xP1'
	,'Starpoint' => '2xP2'
	
	,'Bereg-Lirta_IV' => array('2xP1','2xP2')
	,'Bereg' => '2xP1'
	,'Lirta' => '2xP2'
	,'Lirta IV' => '2xP2'
	,'Lirta_IV' => '2xP2'
	
	,'Tequran-Torkan' => array('2xP1','2xP2')
	,'Tequ\'ran' => '2xP1'
	,'Tequran' => '2xP1'
	,'Torkan' => '2xP2'
	
	,'Coorneeq-Resculon' => array('2xP1','2xP2')
	,'Coorneeq' => '2xP1'
	,'Resculon' => '2xP2'
	
	,'Lazar-Sakulag' => array('2xP1','2xP2')
	,'Lazar' => '2xP1'
	,'Sakulag' => '2xP2'

	,'Dal_Bootha-Xxehan' => array('2xP1','2xP2')
	,'Dal Bootha' => '2xP1'
	,'Dal_Bootha' => '2xP1'
	,'Xxehan' => '2xP2'

	,'Arnor-Lor' => array('2xP1','2xP2')
	,'Arnor' => '2xP1'
	,'Lor' => '2xP2'

	,'Saudor' => 'P1'
	,'Vefut II' => 'P1'
	,'Vefut_II' => 'P1'
	,'Tar\'mann' => 'P1'
	,'Tarmann' => 'P1'
	,'Mehar Xull' => 'P1'
	,'Mehar_Xull' => 'P1'
	,'Thibah' => 'P1'
	,'Quann' => 'WHP1'
	,'Wellon' => 'P1'
	,'Lodor' => 'WHP1'

	
	,'Abyz-Fria' => array('2xP1','2xP2')
	,'Abyz' => '2xP1'
	,'Fria' => '2xP2'
	
	,'Mellon-Zohbat' => array('2xP1','2xP2')
	,'Mellon' => '2xP1'
	,'Zohbat' => '2xP2'
	
	,'Centauri-Gral' => array('2xP1','2xP2')
	,'Centauri' => '2xP1'
	,'Gral' => '2xP2'
	
	,'Qucenn-Rarron' => array('2xP1','2xP2')
	,'Qucen\'n' => '2xP1'
	,'Qucenn' => '2xP1'
	,'Rarron' => '2xP2'
	
	,'Arinam-Meer' => array('2xP1','2xP2')
	,'Arinam' => '2xP1'
	,'Meer' => '2xP2'
	
	,'Ashtroth-Loki-Abaddon' => array('3xP1','3xP2','3xP3')
	,'Ashtroth' => '3xP1'
	,'Loki' => '3xP2'
	,'Abaddon' => '3xP3'
	
	,'Capha' => 'P1'
	,'Elnath' => 'P1'
	,'Garbozia' => 'P1'
	,'Hopes_End' => 'P1'
	,'Hopes End' => 'P1'
	,'Lesab' => 'P1'

	,'Lisis-Velnor' => array('2xP1','2xP2')
	,'Lisis' => '2xP1'
	,'Velnor' => '2xP2'
	
	,'Mirage' => 'P1'
	,'Nexus' => 'NXP1'
	,'Mallice' => 'NXP1'
	,'Perimeter' => 'P1'
	,'Primor' => 'P1'
	
	,'Rigel' => array('3xP1','3xP2','3xP3')
	,'Rigel I' => '3xP1'
	,'Rigel II' => '3xP2'
	,'Rigel III' => '3xP3'
	,'Rigel_I' => '3xP1'
	,'Rigel_II' => '3xP2'
	,'Rigel_III' => '3xP3'
	
	,'Sumerian-Arcturus' => array('2xP1','2xP2')
	,'Sumerian' => '2xP1'
	,'Arcturus' => '2xP2'
	
	,'Tsion-Bellatrix' => array('2xP1','2xP2')
	,'Tsion' => '2xP1'
	,'Bellatrix' => '2xP2'
	
	,'Vega' => array('2xP1','2xP2')
	,'Vega_Minor' => '2xP1'
	,'Vega_Major' => '2xP2'
	
	,'HS-Winnu' => 'HSP1'
	,'Winnu' => 'HSP1'
	
	,'HS-Yin' => 'HSP1'
	,'Darien' => 'HSP1'
	
	,'HS-Saar' => array('HS2xP1','HS2xP2')
	,'Lisis II' => 'HS2xP1'
	,'Lisis_II' => 'HS2xP1'
	,'Ragh' => 'HS2xP2'

	,'HS-Muaat' => 'HSP1'
	,'Muaat' => 'HSP1'	
);

$TI3WM_planetCodePos = array(
	'P1' => array(216, 188)
	,'WHP1' => array(140, 114)
	,'2xP1' => array(140, 114)
	,'2xP2' => array(290, 260)
	,'HSP1' => array(216, 160)
	,'HS2xP1' => array(140, 144)
	,'HS2xP2' => array(314, 190)
	,'HS3xP1' => array(195, 91)
	,'HS3xP2' => array(316, 194)
	,'HS3xP3' => array(142, 235)
	,'3xP1' => array(216, 82)
	,'3xP2' => array(302, 229)
	,'3xP3' => array(123, 240)
	,'NXP1' => array(167, 163)
);

$TI3WM_planetXOffset = array(
	array(0,0)
	,array(37, -37)
	,array(37, 37)
	,array (-37, 37)
	,array(-37, -37)
);

$TI3WM_planetRadius = 37;
$TI3WM_planetCodePositions = array ( array(), array ( 0,0), array ( 0,37, 0,-37), array(0,37, 37,-37, -37,-37), array(37,37 ,37,-37, -37,-37, -37,37) );
$TI3WM_planetOffset = array ( array(0,0), array ( 37,37), array ( 37,-37), array(-37,-37), array(-37,37));

$TI3WM_redirects = array();

$TI3WM_imgSize = array(
	'Destroyer' => array(42, 56)
	,'Cruiser' => array(55, 105)
	,'Dreadnaught' => array(79, 159)
	,'Warsun' => array(135, 113)
	,'Carrier' => array(50, 139)
	,'Fighter' => array(50, 36)
	,'GF' => array(48, 57)
	,'PDS' => array(67, 49)
	,'Spacedock' => array(76, 78)
	,'Admiral' => array(30, 30)
	,'Agent' => array(30, 30)
	,'Diplomat' => array(30, 30)
	,'General' => array(30, 30)
	,'Scientist' => array(30, 30)
	,'LgRnd' => array(60, 60)
	,'SmRnd' => array(30, 30)
	,'CC' => array(100, 88)
	,'High_Alert' => array(99, 99)
	,'Space_Mines' => array(105, 94)
	,'Wormhole_A' => array(97, 100)
	,'Wormhole_B' => array(97, 100)
	,'Wormhole_C' => array(100, 103)
	,'Wormhole_L' => array(95, 99)
	,'Custodians-Fighter3' => array(85, 85)
	,'Custodians-GF2' => array(85, 85)
);

function TI3WM_ImageRedirect( $content, $args, &$parser )
{
	global $TI3WM_redirects;

	// redirect from URL to Redirect
	// URL is an image located in the wikimap
	
	$URL = $args['image'];
	$Redirect = $args['redirect'];
	$Scale = $args['scale'];
	
	$Home = '/home/tithrwik/public_html';
	
	// Changed: 20 May 09
	// To provide support for automatic symbol linking of wikimap images
	// Removes duplication and update issues that arise from keeping two copies of an image
	// one in wikimap and one in the wiki
	
	// if the URL doesn't start with http://www.ti3wiki.org prepend it
	if(strcmp(substr($URL,0,strlen('http://www.ti3wiki.org')), 'http://www.ti3wiki.org') != 0)
		$URL = "http://www.ti3wiki.org/wikimap/${Scale}/$URL";
	
	// if this is placed in an images page, try to create a symbolic link
	$thisTitle = $parser->getTitle();
	if($thisTitle->getNamespace() == NS_IMAGE)
	{
		if(strlen($Redirect) == 0)
		{
				$imgPage = new ImagePage($thisTitle);
				$localExistingLink  = $imgPage->img->getURL();
		}
		else
		{
			// only allow redirects from wiki stored images
			// existing file
			if(strpos($Redirect, 'http://www.ti3wiki.org/images') === false)
				$localExistingLink = '/images' . $Redirect;
			else
				$localExistingLink = '/images' . substr($Redirect, strlen('http://www.ti3wiki.org/images'));
		}
		
		$safeLocalExistingLink = $localExistingLink;
		$localExistingLink = $Home . $localExistingLink;
		
		// local link (http://www.ti3wiki.org/wikimap/$Scale/$URL)
		$localLinkTarget = substr($URL,strlen('http://www.ti3wiki.org'));
		
		$safeLocalLinkTarget = $localLinkTarget;
		$localLinkTarget = $Home . $localLinkTarget;

		$retv .= "TI3WM_Redirect:localExistingLink=$safeLocalExistingLink localLinkTarget=$safeLocalLinkTarget\n";
		
		// if the source file exists
		if(file_exists($localExistingLink))
		{
			$retv .= "TI3WM_Redirect:found $safeLocalExistingLink\n";
			if(is_link($localLinkTarget))
			{
				$retv .= "TI3WM_Redirect:found $safeLocalLinkTarget as existing link\n";
				// if link already points to where we want then do nothing
				if(readlink($localLinkTarget) === $localExistingLink)
				{
					$retv .= "TI3WM_Redirect:read $safeLocalLinkTarget as already pointing at $safeLocalExistingLink\n";
				}
				else
				{
					// successfully read the symbolic link it just doesn't point where we want
					if($linkTargetAlreadyLinked !== false)
					{
						$retv .= "TI3WM_Redirect: $safeLocalLinkTarget already existing symbolic link. Recreating symbolic link as $safeLocalExistingLink->$safeLocalLinkTarget\n";
						unlink($localLinkTarget);
						symlink($localExistingLink, $localLinkTarget);
					}
				}
			}
			// failed to read the symbolic link: might be an existing file or might not exist
			else
			{
				// if file exists and is not a symbolic link
				if(file_exists($localLinkTarget))
				{
					$retv .= "TI3WM_Redirect: Error: $safeLocalLinkTarget is already existing file, can't overwrite with symbolic link. Use redirect tag in global includes of map to override default file.\n";
				}
				else
				{
					$retv .= "TI3WM_Redirect: $safeLocalLinkTarget doesn't exist, creating symbolic link $safeLocalExistingLink->$safeLocalLinkTarget\n";
					symlink($localExistingLink, $localLinkTarget);
				}
			}
		}
	//	dout($retv);

		return "<pre>$retv</pre>";	
	}
	// Create a new redirect by setting the key to the image URL to the redirected URL
	$TI3WM_redirects[$URL] = $Redirect;
}
	
function TI3WM_GetPlanetLeftTop($args)
{
	global $TI3WM_planetCodes, $TI3WM_planetCodePos, $TI3WM_planetOffset, $TI3WM_planetCount;
	
	$Planet = $args['planet'];
	$Where = $args['where'];
	
	// If a planet value was specified
	if(strlen($Planet) > 0)
		// If the $Planet specified has a planetCode assigned use that, otherwise just treat $Planet as the code
		$PlanetCode = isset($TI3WM_planetCodes[$Planet]) ? $TI3WM_planetCodes[$Planet] : $Planet;
	// If no planet was specified then assign the name of the last tile used
	else 
	{
		if(is_array($TI3WM_planetCodes[$args['tile']]))
		{
			$a = $TI3WM_planetCodes[$args['tile']];
			$PlanetCode = $a[$TI3WM_planetCount++];
		}
		else $PlanetCode=$TI3WM_planetCodes[$args['tile']];
	}
	
	// Lookup the x,y values of the planet code plus the x,y offset
	$Left = $TI3WM_planetCodePos[$PlanetCode][0] + $TI3WM_planetOffset[$Where][0] + $args['x'];
	$Top = $TI3WM_planetCodePos[$PlanetCode][1] + $TI3WM_planetOffset[$Where][1] + $args['y'];
	
	return array($Left, $Top);
}

function TI3WM_GetLeftTop($args)
{
	global $TI3WM_planetCodes, $TI3WM_planetCodePos, $TI3WM_planetOffset, $TI3WM_clockPos, $TI3WM_clockAngle;

	$Where = $args['where'];
	$Planet = $args['planet'];
	$Radius = $args['radius'];
	$Angle = $args['angle'];
	$X = $args['x'];
	$Y = $args['y'];
	
	// If no planet is specified 
	if(strlen($Planet) == 0)
	{
		if(is_numeric($Where))
			$Angle = $Where * 30;
		else 
		{
			switch($Where)
			{
				case 'c' :
				case 'center' :
					return array(216,188);
					break;
				default:
				case 'notset' :
					if($Angle == 'notset')
						return array($X,$Y);
			}
		}

		$Angle -= 90;
		
		$Left = (cos(deg2rad($Angle)) * 216 * $Radius) + 216 + $X;
		$Top = (sin(deg2rad($Angle)) * 188 * $Radius) + 188 + $Y;
	}
	else
	{
		// If the $Planet specified has a planetCode assigned use that, otherwise just treat $Planet as the code
		$PlanetCode = isset($TI3WM_planetCodes[$Planet]) ? $TI3WM_planetCodes[$Planet] : $Planet;
		
		// Lookup the x,y values of the planet code plus the x,y offset
		$Left = $TI3WM_planetCodePos[$PlanetCode][0] + $TI3WM_planetOffset[$Where][0] + $args['x'];
		$Top = $TI3WM_planetCodePos[$PlanetCode][1] + $TI3WM_planetOffset[$Where][1] + $args['y'];
	}
	return array($Left, $Top);
}
	
function TI3WM_Tile( $content, $args, &$parser )
{
	global $TI3WM_redirects, $TI3WM_planetCount, $TI3WM_tileInProgress, $TI3WM_mapInProgress;
	
	// Cache local arguments
	$Tile = $args['tile'];
	$Label = $args['label'];
	$LabelColor = $args['labelcolor'];
	$LabelSize = $args['labelsize'];
	$PieceScale = $args['piecescale'];
	$Scale = $args['scale'];
	$Format = $args['format'];
	$Output = $args['output'];
	if($Output == 'html')
	{
		$indent = ' ';
		$endl = "\n";
	}

//	$URL = "Tile-$Tile.$Format";
	$URL = "Tile-$Tile.gif";

	$TI3WM_planetCount = 0;
	$TI3WM_tileInProgress = true;
	$content = TI3WM_Parse( $content, $args, &$parser);
	if(strlen($content) > 0)
		$inside = "$content";
	
	if(strlen($Label) > 0)
	{
		$inside .= TI3WM_labelHTML(	$Label
									,$LabelSize // Font PX
									,432 // Width
									,376 // Height
									,'center' // text align
									,218 // Left
									,188 // Top
									,true // Center
									,0 // Z
									,1 // PieceScale
									,$Scale
									,$args
								);
	}
	$TI3WM_tileInProgress = false;
	
	$a = TI3WM_GetImageInfo($URL, $Scale);
	$URL = $a[0];
	$Width = $a[2];
	$Height = $a[3];
	
	$ScaledWidth = $Width * $Scale;
	$ScaledHeight = $Height * $Scale;

	
	if(array_key_exists($URL, $TI3WM_redirects))
		$URL = $TI3WM_redirects[$URL];
		
	
	$sizeHTML = "width: ${ScaledWidth}px;height: ${ScaledHeight}px;z-index: 0;";

	if(strlen($sizeHTML) > 0 || strlen($relativeHTML) > 0)
		$styleHTML = "style=\"$relativeHTML$sizeHTML\" ";
		
	if(strlen($inside) > 0)
		$retv = "<img ${styleHTML}src=\"$URL\">$endl$inside</img>$endl";
	else $retv = "<img ${styleHTML}src=\"$URL\"/>$endl";
	
	if(!$TI3WM_mapInProgress)
		$retv = "<div style=\"position: relative;\">$endl$retv</div>$endl";
	
	return $retv;
}
	
function TI3WM_Unit( $content, $args, &$parser )
{
	// Cache local arguments
	$Type = $args['unit'];
	$Race = $args['race'];
	$Quantity = $args['quantity'];
	$Color = $args['color'];
	$Damaged = $args['damaged'];
	$Scale = $args['scale'];
	$Format = $args['format'];
	$Center = $args['center'];
	
	// _mapUnit
	$p = TI3WM_GetLeftTop($args);
	$Left = $p[0];
	$Top = $p[1];
	
	if(strlen($Type)>0 && ctype_digit($Type[strlen($Type)-1]))
		$BaseType = substr($Type, 0, strlen($Type)-1);
	else $BaseType = $Type;
	
	switch($BaseType)
	{
		case 'Admiral' :
		case 'Agent' :
		case 'Diplomat' :
		case 'General' :
		case 'Scientist' :
			if(strlen($Race) > 0)
				$retv .= TI3WM_RaceLeaderHTML($Race, $Type, $Left, $Top, $Center, $Scale, $Format, $args);
			else $retv.= TI3WM_MiniLeaderHTML($Type, $Left, $Top, $Center, $Scale, $Format, $args);
			break;
		default:
			$retv .= TI3WM_UnitHTML($Type, $Quantity, ($Quantity > 1), $Color, $Damaged, $Left, $Top, $Center, $Scale, $Format, $args);
	}

	return $retv;
}

function TI3WM_Planet( $content, $args, &$parser )
{
	global $TI3WM_planetRadius, $TI3WM_planetCodePositions;
	
	// Cache local arguments
	$Planet = $args['planet'];
	$Race = $args['race'];
	$Color = $args['color'];
	$Spacedock = $args['spacedock'];
	$Fighters = $args['fighters'];
	$GF = $args['gf'];
	$ST = $args['st'];
	$PDS = $args['pds'];
	$DS = $args['ds'];
	$Artifact = $args['artifact'];
	$Scale = $args['scale'];
	$Format = $args['format'];
	$PlanetFlagType = $args['flag'];
	$PieceScale = $args['piecescale'];
	
	// _mapPlanet
	$p = TI3WM_GetPlanetLeftTop($args);
	$PX = $p[0];
	$PY = $p[1];
	
	$tokenCount = 0;
	
//	$ShowRace = $Race != '' && $PlanetFlagType != '' || $PlanetFlagType != 'none';
//	$ShowRace = $Race != 'notset' && $PlanetFlagType != 'none';
	$ShowRace = strlen($Race) > 0 && $PlanetFlagType != 'none';
	$ShowDS = $DS != '';
	$ShowArtifact = $Artifact != '';
	if($ShowRace) $tokenCount++;
	if($ShowDS) $tokenCount++;
	if($ShowArtifact) $tokenCount++;
	
	$tokenIDX = 0;
	
	if($ShowRace)
	{
		if($Planet == 'Tsion' || $Planet == 'Sumerian')
		{
			if($Planet == 'Tsion')
			{
				$Left = 218;
				$Top = 77;
			}
			else
			if($Planet == 'Sumerian')
			{
				$Left = 221;
				$Top = 73;
			}
			
			$retv .= TI3WM_FlagHTML('Wavy',$Race, $Left, $Top, true, $Scale, $Format, $args);		
		}
		else
		{
			$Left = $PX + ($TI3WM_planetCodePositions[$tokenCount][$tokenIDX] * $PieceScale);
			$Top = $PY + ($TI3WM_planetCodePositions[$tokenCount][$tokenIDX+1] * $PieceScale);
			
			$retv .= TI3WM_FlagHTML($PlanetFlagType,$Race, $Left, $Top, true, $Scale, $Format, $args);
			
			$tokenIDX+=2;
		}
	};
	if($ShowDS)
	{
		$Left = $PX + ($TI3WM_planetCodePositions[$tokenCount][$tokenIDX] * $PieceScale);
		$Top = $PY + ($TI3WM_planetCodePositions[$tokenCount][$tokenIDX+1] * $PieceScale);

		$retv .= TI3WM_dsHTML($DS, $Left, $Top, true, $Scale, $Format, $args);
		$tokenIDX+=2;
	}
	if($ShowArtifact)
	{
		$Left = $PX + ($TI3WM_planetCodePositions[$tokenCount][$tokenIDX] * $PieceScale);
		$Top = $PY + ($TI3WM_planetCodePositions[$tokenCount][$tokenIDX+1] * $PieceScale);

		$retv .= TI3WM_ArtifactHTML($Artifact, $Left, $Top, true, $Scale, $Format, $args);
		$tokenIDX+=2;
	}
	
	$ShowSpacedock = $Spacedock > 0;
	$ShowGF = $GF > 0 || $ST > 0;
	$ShowPDS1 = $PDS > 0;
	$ShowPDS2 = $PDS > 1;
	
	$Radius = $TI3WM_planetRadius * $PieceScale;
	if($ShowSpacedock)
	{
		$Left = $PX - $Radius;
		$Top = $PY - $Radius;
		
		$retv .= TI3WM_UnitHTML('Spacedock', 1, false, $Color, '', $Left, $Top, true, $Scale, $Format, $args);
		if($Fighters > 0)
		{
			$Left -= 20*$PieceScale;
			$Top -= 60*$PieceScale;
			$retv .= TI3WM_UnitHTML('Fighter', $Fighters, true, $Color, '', $Left, $Top, true, $Scale, $Format, $args);
		}
	}
	if($ShowGF)
	{
		$Left = $PX + $Radius;
		$Top = $PY - $Radius;
		
		$retv .= TI3WM_gfHTML($GF, $ST, $Color, $Left, $Top, true, $Scale, $Format, $args);
	}
	if($ShowPDS1)
	{
		$Left = $PX + $Radius;
		$Top = $PY + $Radius;
		
		$retv .= TI3WM_UnitHTML('PDS', 1, false, $Color, '', $Left, $Top, true, $Scale, $Format, $args);
	}
	if($ShowPDS2)
	{
		$Left = $PX - $Radius;
		$Top = $PY + $Radius;
		
		$retv .= TI3WM_UnitHTML('PDS', 1, false, $Color, '', $Left, $Top, true, $Scale, $Format, $args);
		$unitIDX+=2;
	}
	
	return $retv;

}

function TI3WM_Leader( $content, $args, &$parser )
{
	// Cache local arguments
	$Type = $args['leader'];
	$Race = $args['race'];
	$Scale = $args['scale'];
	$Format = $args['format'];
	$Center = $args['center'];
	
	$p = TI3WM_GetLeftTop($args);
	$Left = $p[0];
	$Top = $p[1];
	
	if(strlen($Race) > 0)
//	if($Race != 'notset')
		$retv .= TI3WM_RaceLeaderHTML($Race, $Type, $Left, $Top, $Center, $Scale, $Format, $args);
	else $retv.= TI3WM_MiniLeaderHTML($Type, $Left, $Top, $Center, $Scale, $Format, $args);

	return $retv;
	
}

function TI3WM_CC( $content, $args, &$parser )
{
	// Cache local arguments
	$Race = $args['race'];
	$Scale = $args['scale'];
	$Format = $args['format'];
	$Center = $args['center'];
	
	// _mapCC
	$p = TI3WM_GetLeftTop($args);
	$Left = $p[0];
	$Top = $p[1];
	
	$Content = "CC-${Race}.gif";
	
	return TI3WM_imgHTML($Left, $Top, $Center, 1, $Content, $Scale, $args);
}

function TI3WM_Token( $content, $args, &$parser )
{
	$Type = $args['token'];
	$Scale = $args['scale'];
	$Format = $args['format'];
	$Center = $args['center'];
	
	// _mapToken
	$p = TI3WM_GetLeftTop($args);
	$Left = $p[0];
	$Top = $p[1];
	
	return TI3WM_TokenHTML($Type, $Left, $Top, $Center, $Scale, $Format, $args);
}

function TI3WM_Flag( $content, $args, &$parser )
{
	// Cache local arguments
	$Type = $args['flag'];
	$Race = $args['race'];
	$Scale = $args['scale'];
	$Format = $args['format'];
	$Center = $args['center'];
	
	$p = TI3WM_GetLeftTop($args);
	$Left = $p[0];
	$Top = $p[1];
	
	if(($Type != 'LgRnd' && $Type != 'SmRnd' && $Type != 'Wavy') || $Type == '')
		$Type = 'LgRnd';
		
	return TI3WM_FlagHTML($Type, $Race, $Left, $Top, $Center, $Scale, $Format, $args);
}

function TI3WM_Image( $content, $args, &$parser )
{
	// Cache local arguments
	$URL = $args['url'];
//	$Width = $args['width'];
//	$Height = $args['height'];
	$Z = isset($args['z']) ? $args['z'] : 2;
	$Scale = $args['scale'];
	$Center = $args['center'];
	$Quantity = $args['quantity'];
	$Format = $args['format'];
	
	// _mapImg
	$p = TI3WM_GetLeftTop($args);
	$Left = $p[0];
	$Top = $p[1];
	
	$URL = $args['urlroot'] . $URL;
	
	$args['scale'] = TI3WM_GetImageScale($Scale);
	
	$keys = array_keys($args);
	for($i=0;$i<count($keys);$i++)
		$keys[$i] = '$' . $keys[$i];
	
	$URL = str_ireplace(	$keys
							,array_values($args)
							,$URL
						);
						
	$args['scale'] = $Scale;
	
	return TI3WM_CustomImageHTML($URL, $Z, $Left, $Top, $Center, $Scale, $Quantity, ($Quantity > 1), $Format, $args);
}

function TI3WM_Carrier( $content, $args, &$parser )
{
	return TI3WM_renderShip(
			'Carrier'
			,$args['color']
			,$args['damaged']
			,$args['fighters']
			,$args['gf']
			,$args['st']
			,$args['pds']
			,$args['x']
			,$args['y']
			,$args['scale']
			,$args['format']
			,$args
		);
}

function TI3WM_Warsun( $content, $args, &$parser )
{
	return TI3WM_renderShip(
			'Warsun'
			,$args['color']
			,$args['damaged']
			,$args['fighters']
			,$args['gf']
			,$args['st']
			,$args['pds']
			,$args['x']
			,$args['y']
			,$args['scale']
			,$args['format']
			,$args
		);
}
	
function TI3WM_Destroyer( $content, $args, &$parser )
{
	return TI3WM_renderShip(
			'Destroyer'
			,$args['color']
			,$args['damaged']
			,$args['fighters']
			,$args['gf']
			,$args['st']
			,$args['pds']
			,$args['x']
			,$args['y']
			,$args['scale']
			,$args['format']
			,$args
		);
}

function TI3WM_Cruiser( $content, $args, &$parser )
{
	return TI3WM_renderShip(
			'Cruiser'
			,$args['color']
			,$args['damaged']
			,$args['fighters']
			,$args['gf']
			,$args['st']
			,$args['pds']
			,$args['x']
			,$args['y']
			,$args['scale']
			,$args['format']
			,$args
		);
}

function TI3WM_Dreadnaught( $content, $args, &$parser )
{
	return TI3WM_renderShip(
			'Dreadnaught'
			,$args['color']
			,$args['damaged']
			,$args['fighters']
			,$args['gf']
			,$args['st']
			,$args['pds']
			,$args['x']
			,$args['y']
			,$args['scale']
			,$args['format']
			,$args
		);
}

function TI3WM_Ship( $content, $args, &$parser )
{
	return TI3WM_renderShip(
			$args['ship']
			,$args['color']
			,$args['damaged']
			,$args['fighters']
			,$args['gf']
			,$args['st']
			,$args['pds']
			,$args['x']
			,$args['y']
			,$args['scale']
			,$args['format']
			,$args
		);
}

function TI3WM_renderShip($Type, $Color, $Damaged, $Fighters, $GF, $ST, $PDS, $X, $Y, $Scale, $Format, $args)
{
	global $TI3WM_imgSize;
	$Center = $args['center'];
	$PieceScale = $args['piecescale'];

	$p = TI3WM_GetLeftTop($args);
	$Left = $p[0];
	$Top = $p[1];
	
	$retv = TI3WM_UnitHTML( $Type, 1, false, $Color, $Damaged, $Left, $Top, $Center, $Scale, $Format, $args);
	
	$a = TI3WM_GetImageInfo("Unit-$Color-${Type}.${Format}", $Scale);
	$shipWidth = $a[2] * $PieceScale;
	$shipHeight = $a[3] * $PieceScale;
	
	if($GF > 0 || $ST > 0)
	{
		$a = TI3WM_GetImageInfo("Unit-$Color-GF.${Format}", $Scale);
		$gfWidth = $a[2] * $PieceScale;
		$gfHeight = $a[3] * $PieceScale;
		
		$retv .= TI3WM_gfHTML( 	$GF
								,$ST
								,$Color 
								,$Left + ($shipWidth/2) + ($gfWidth/2) 
								,$Top - $shipHeight/4
								,$Center
								,$Scale 
								,$Format
								,$args
							);
	}
	
	if($Fighters > 0)
	{
		$a = TI3WM_GetImageInfo("Unit-$Color-Fighter.${Format}", $Scale);
		$fgWidth = $a[2] * $PieceScale;
		$fgHeight = $a[3] * $PieceScale;
		
		$retv .= TI3WM_UnitHTML( 	'Fighter' 
									,$Fighters 
									,($Fighters > 1) 
									,$Color 
									,'' 
									,$Left + ($shipWidth/2) + ($fgWidth/2) 
									,$Top + $shipHeight/4
									,$Center
									,$Scale 
									,$Format
									,$args
								);
	}
	
	if($PDS > 6) $PDS = 6;
	for($i=0;$i<$PDS;$i++)
	{
		$a = TI3WM_GetImageInfo("Unit-$Color-PDS.${Format}", $Scale);
		$pdsWidth = $a[2] * $PieceScale;
		$pdsHeight = $a[3] * $PieceScale;
		
		$retv .= TI3WM_UnitHTML( 	'PDS' 
									,1 
									,false 
									,$Color 
									,'' 
									,$Left - ($shipWidth/2) - ($pdsWidth/2)
									,$Top - ($shipHeight/2) + (($pdsHeight + 5)*$i) 
									,$Center
									,$Scale 
									,$Format
									,$args
								);
	}
	
	
	return $retv;
}
	
function TI3WM_RaceLeaderHTML($Race, $Type, $Left, $Top, $Center, $Scale, $Format, $args)
{
	$Content = "Leader-${Race}-${Type}.gif";
	$Z = isset($args['z']) ? $args['z'] : 1;
	
	return TI3WM_imgHTML($Left, $Top, $Center, $Z, $Content, $Scale, $args);
}

function TI3WM_MiniLeaderHTML($Type, $Left, $Top, $Center, $Scale, $Format, $args)
{
	$Content = "MiniLeader-${Type}.${Format}";
	$Z = isset($args['z']) ? $args['z'] : 3;
	
	return TI3WM_imgHTML($Left, $Top, $Center, $Z, $Content, $Scale, $args);
}
	
function TI3WM_FlagHTML($Type, $Race, $Left, $Top, $Center, $Scale, $Format, $args)
{
	$Content = "Flag-${Type}-${Race}.${Format}";
	$Z = isset($args['z']) ? $args['z'] : 1;

	return TI3WM_imgHTML($Left,$Top, $Center, $Z, $Content, $Scale, $args);
}

function TI3WM_TokenHTML($Type, $Left, $Top ,$Center, $Scale, $Format, $args)
{
	$Content = "Token-${Type}.${Format}";
	$Z = isset($args['z']) ? $args['z'] : 1;
	
	return TI3WM_imgHTML($Left, $Top, $Z, $Center, $Content, $Scale, $args);
}

function TI3WM_CustomImageHTML($URL, $Z, $Left, $Top, $Center, $Scale, $Quantity, $ShowQuantity, $Format, $args)
{
	$retv = TI3WM_imgHTML($Left, $Top, $Center, $Z, $URL, $Scale, $args);
	
	if($ShowQuantity)
		$retv .= TI3WM_QuantityBoxHTML($URL, $Quantity, 'right', '', $Left,  $Top, $Z, $Scale, $Format, $args);
		
	return $retv;
}

function TI3WM_ArtifactHTML($Color, $Left, $Top, $Center, $Scale, $Format, $args)
{
	$Content = "Artifact-${Color}.${Format}";
	$Z = isset($args['z']) ? $args['z'] : 1;

	return TI3WM_imgHTML($Left,$Top, $Center, $Z, $Content, $Scale, $args);
}

function TI3WM_dsHTML($Type, $Left, $Top, $Center, $Scale, $Format, $args)
{
	$DSList = array ( 'Biohazard','Radiation','Hostile_Locals', 'Lazax_Survivors', 'Settlers', 'Technological_Society','Natural_Wealth','Industrial_Society','Peaceful_Annexation','Wormhole_Discovery','Native_Intelligence','Hidden_Factory','Hostage_Situation','Automated_Defense_System','Fighter_Ambush');
	$Z = isset($args['z']) ? $args['z'] : 1;
	
	$DS_Image = $Type;
	
	$DS = explode('-',$Type);
	foreach($DSList as $i)
	{
		if(strncasecmp($i,$DS[0],strlen($DS[0]))==0)
		{
			$DS_Image = $i;
			if(count($DS)>1)
				$DS_Image .= ('-' . $DS[1]);
			break;
		}
	};
	
	$Content = "DistantSuns-${DS_Image}.${Format}";
	return TI3WM_imgHTML($Left, $Top, $Center, $Z, $Content, $Scale, $args);
}
	
function TI3WM_gfHTML($GF, $ST, $Color, $Left, $Top, $Center, $Scale, $Format, $args)
{
	$PieceScale = $args['piecescale'];
	$Z = isset($args['z']) ? $args['z'] : 2;
	
	$retv .= TI3WM_UnitHTML('GF', $GF, true, $Color, '', $Left, $Top, $Center, $Scale, $Format, $args);
	
	if($ST > 0)
	{
		$Content = "Unit-GF-ShockTrooperFlag.gif";

		$retv .= TI3WM_imgHTML($Left, $Top, $Center, $Z, $Content, $Scale, $args);
		
		$retv .= TI3WM_QuantityBoxHTML($Content, $ST, 'top', 'ST', $Left + (10*$PieceScale),  $Top, $Z, $Scale, $Format, $args);
	}
	
	return $retv;
}
	
function TI3WM_UnitHTML($Type, $Quantity, $ShowQuantity, $Color, $Damaged, $Left, $Top, $Center, $Scale, $Format, $args)
{
	global $TI3WM_imgSize;
	$Z = isset($args['z']) ? $args['z'] : 2;
	
	$Glow = $args['glow'];

	if($Format == 'png' && strlen($Glow) > 0)
	{
		$Content = "UnitGlow-${Glow}-${Type}.png";
		
		$retv .= TI3WM_imgHTML($Left, $Top, $Center, $Z, $Content , $Scale, $args);
	}
	
	$Content = "Unit-${Color}-${Type}.${Format}";
	
	$Width = $TI3WM_imgSize[$Type][0];
	
	$retv .= TI3WM_imgHTML($Left,$Top, $Center, $Z, $Content , $Scale, $args);
	
	if($ShowQuantity)
		$retv .= TI3WM_QuantityBoxHTML($Content, $Quantity, 'right', '', $Left,  $Top, $Z, $Scale, $Format, $args);
	
	if(strlen($Damaged) > 0 && $Damaged != 0)
	{
		$Content = "Unit-Damage${Damaged}.gif";
		
		$retv .= TI3WM_imgHTML($Left, $Top, $Center, $Z, $Content, $Scale, $args);
	}
	
	return $retv;
}
	
function TI3WM_QuantityBoxHTML($PieceURL, $Quantity, $bxAlign, $bxRightIcon, $Left, $Top, $Z, $Scale, $Format, $args)
{
	$PieceScale = $args['piecescale'];

	$a = TI3WM_GetImageInfo($PieceURL, $Scale);
	$PieceWidth = $a[2] * $PieceScale;
	$PieceHeight = $a[3] * $PieceScale;
	
	$Digits = strlen($Quantity);
	
	switch($bxAlign)
	{
		case 'top' :
			$bxArrowURL = "QuantityBox-Arrow-Down.${Format}";
			break;
		default:
//		case 'right' :
			$bxArrowURL = "QuantityBox-Arrow-Left.${Format}";
			break;
	}
	$bxLeftURL = "QuantityBox-Left.${Format}";
	$bxMiddleURL = "QuantityBox-Middle.${Format}";
	$bxRightURL = "QuantityBox-Right";
	if(strlen($bxRightIcon)>0)
		$bxRightURL .= "-$bxRightIcon";
	$bxRightURL .= ".$Format";
	
	$a = TI3WM_GetImageInfo($bxArrowURL, $Scale);
	$bxArrowWidth = $a[2] * $PieceScale;
	$bxArrowHeight = $a[3] * $PieceScale;
	$a = TI3WM_GetImageInfo($bxLeftURL, $Scale);
	$bxLeftWidth = $a[2] * $PieceScale;
	$bxLeftHeight = $a[3] * $PieceScale;
	$a = TI3WM_GetImageInfo($bxMiddleURL, $Scale);
	$bxMiddleWidth = $a[2] * $PieceScale;
	$a = TI3WM_GetImageInfo($bxRightURL, $Scale);
	$bxRightWidth = $a[2] * $PieceScale;
	
	$bxWidth = ($bxLeftWidth + ($bxMiddleWidth * ($Digits-1)) + $bxRightWidth)/$PieceScale;

	switch($bxAlign)
	{
		case 'top' :
			$bxArrowOverlap = 4 * $PieceScale;
			$bxArrowLeft = $Left;
			$bxArrowTop = $Top - ($PieceHeight/2) - ($bxArrowHeight/2);
			
			$bxTop = $bxArrowTop - ($bxArrowWidth/2) + $bxArrowOverlap - ($bxLeftHeight/2);
			$bxLeft = $Left - ($PieceWidth/2);
			
			break;
		default:
//		case 'right' :
			$bxArrowOverlap = 4 * $PieceScale;
			$bxArrowLeft = $Left + ($PieceWidth/2)+ ($bxArrowWidth/2);
			$bxArrowTop = $Top;
			
			$bxLeft = $bxArrowLeft + ($bxArrowWidth/2) - $bxArrowOverlap + ($bxLeftWidth/2);
			$bxTop = $Top;
			break;
	}
	
	$x = $bxLeft;
	
	$retv .= TI3WM_imgHTML($x, $bxTop, true, $Z, $bxLeftURL, $Scale, $args);
	$lastWidth = $bxLeftWidth;
	for($i=0;$i<$Digits-1;$i++)
	{
		$x += ($lastWidth/2) + ($bxMiddleWidth/2);
		$retv .= TI3WM_imgHTML($x, $bxTop, true, 2, $bxMiddleURL, $Scale, $args);
		$lastWidth = $bxMiddleWidth;
	}
	$x += ($lastWidth/2) + ($bxRightWidth/2);
	$retv .= TI3WM_imgHTML($x, $bxTop, true, $Z, $bxRightURL, $Scale, $args);

	$retv .= TI3WM_imgHTML($bxArrowLeft, $bxArrowTop, true, $Z, $bxArrowURL, $Scale, $args);

	$labelWidth = $bxWidth;
	$labelHeight = $bxLeftHeight;
	
	$C = 3 * $PieceScale;
	$labelLeft = $bxLeft + $C + ($bxLeftWidth/2);
	if($Digits > 1)
		$labelLeft += ($bxMiddleWidth/2);
	if($Digits > 2)
		$labelLeft += (($Digits - 2) * $bxMiddleWidth);
	
	$labelTop = $bxTop;
	
	$retv .= TI3WM_LabelHTML(	$Quantity
								,42 // Font PX
								,$labelWidth // Width
								,$labelHeight // Height
								,'center' // text align
								,$labelLeft // Left
								,$labelTop // Top
								,true // center
								,$Z // Z
								,$PieceScale
								,$Scale
								,$args
							);
	
	return $retv;
}

function TI3WM_LabelHTML($Label, $FontPX, $Width, $Height, $Align, $Left, $Top, $Center, $Z, $PieceScale, $Scale, $args)
{
	global $TI3WM_tileInProgress, $TI3WM_mapInProgress;
	
	$Output = $args['output'];
	
	if($Output == 'html')
	{
		$indent = ' ';
		$endl = "\n";
	}
	
	$Left *= $Scale;
	$Top *= $Scale;
	
	$Scale *= $PieceScale;
	
	$ScaledFontPX = $FontPX * $Scale;
	$ScaledWidth = $Width * $Scale;
	$ScaledHeight = $Height * $Scale;
	
	if($Center && $Width > 0 && $Height > 0)
	{
		$Left -= $ScaledWidth/2;
		$Top -= $ScaledHeight/2;
		$Top += ($ScaledHeight - $ScaledFontPX)/2;
	}
	
	$LabelColor = $args['labelcolor'];
	
	if($TI3WM_tileInProgress || $TI3WM_mapInProgress)
		$posHTML = "position: absolute; left: ${Left}px; top: ${Top}px;";
	if($Width !=0)
		$widthHTML = "width: ${ScaledWidth}px;";
	$retv .= "<div style=\"${posHTML}${widthHTML}font-weight: bold;text-align: ${Align};z-index: ${Z};color: ${LabelColor}; font-size: ${ScaledFontPX}px;line-height: ${ScaledFontPX}px;\">${Label}</div>$endl";
	return $retv;
}
	
function TI3WM_imgHTML($Left, $Top, $Center, $Z, $URL, $Scale, $args)
{
	global $TI3WM_redirects, $TI3WM_tileInProgress;
	
	$PieceScale = $args['piecescale'];
	$Output = $args['output'];
	$ToolTip = $args['tooltip'];
	
	if($Output == 'html')
	{
		$indent = ' ';
		$endl = "\n";
	}

	$Left *= $Scale;
	$Top *= $Scale;
	
	$Scale *= $PieceScale;

	$a = TI3WM_GetImageInfo($URL, $Scale);
	$URL = $a[0];
	$ImgScale = $a[1];
	$Width = $a[2];
	$Height = $a[3];
	
	$ScaledWidth = $Width * $Scale;
	$ScaledHeight = $Height * $Scale;
	
	if($ImgScale != $Scale)
		$sizeHTML = "width: ${ScaledWidth}px; height: ${ScaledHeight}px;";
	
	if($Center)
	{
		$Left -= $ScaledWidth/2;
		$Top -= $ScaledHeight/2;	
	}

	$retv = "$indent<img style=\"";
	if($TI3WM_tileInProgress)
		$retv .= "position: absolute;left: ${Left}px; top: ${Top}px;";
	if(strlen($ToolTip) > 0)
		$ToolTip = " title=\"$ToolTip\"";
	$retv .= "z-index: ${Z};$sizeHTML\" src=\"$URL\"$ToolTip/>$endl";
	
	return $retv;
}
	
function TI3WM_ParsePage( $Page, &$parser )
{
	$title = Title::newFromText( $Page );
	if(!is_object($title))
		return false;

	if(!$title->exists())
	{
		$parser->mOutput->addTemplate($title, 0, 0);
		return false;
	}
		
	$r = Revision::newFromTitle($title);
	if(!is_object($r))
	{
		$parser->mOutput->addTemplate($title, 0, 0);
		return false;
	}
	
	$revId = $r->getId();

	$text = $r->getText();

	$parser->mOutput->addTemplate($title, $title->getArticleID(),$revId);
	
	return TI3WM_ParsePageFromText($revId, $text, $parser);
}

function TI3WM_ParsePageFromText( $revId, $text, &$parser )
{
	global $TI3WM_ParsePageCache;
	
	if(isset($TI3WM_ParsePageCache[$revId]))
		return $TI3WM_ParsePageCache[$revId];
		
	$Width = 1;
	$Height = 1;
	
//	$title = $r->getTitle();
	
//	$text = $r->getText();
	
	$a = preg_split("/(==+\s*)([^=]+)(\s*==+\s*)/i", $text, -1, PREG_SPLIT_DELIM_CAPTURE);

	$count = count($a);
	$SectionNum = 0;
	for($i=0;$i<$count;$i++)
	{
		if(strstr($a[$i], '==') === false)
			continue;
			
		if($i + 3 >= $count)
			break;
			
		$Section = $a[$i+1];
		$SectionNum++;
		$Content = $a[$i+3];
		$i += 3;
		
		$p = explode('-', $Section);
		$x = trim($p[0]);
		$y = trim($p[1]);
		
		if(!is_numeric($x) || !is_numeric($y))
		{
			// Parse for side effects
			if(strlen($Content)>0)
			{
/*				Parser::extractTagsAndParams(	array('global')
											   ,$Content
											   ,$matches 
											);	
				$temp = array();
				if(count($matches))
					TI3WM_ParsePage($Content, $temp, &$parser);*/
//				$temp = array();
//				TI3WM_ParsePage($Content, $temp, &$parser);
				$parser->recursiveTagParse($Content);
			}
			continue;
		}
		
		if($x > 17 || $y > 17) 
			continue;
			
		if($Width <= $x) $Width = $x+1;
		if($Height <= $y) $Height = $y+1;

//		if(strlen($Content)>0)
			$tiles[$x][$y] = array($Section, $Content, $SectionNum);
	}
	
	$TI3WM_ParsePageCache[$revId] = array($Width, $Height, $tiles);
	
	return $TI3WM_ParsePageCache[$revId];

//	dout("TI3WM_PageParse $Width $Height\n");
//	return array($Width, $Height, $tiles);
}

function TI3WM_Map( $content, $args, &$parser )
{
	global $TI3WM_defaultArgs, $TI3WM_indexURL, $TI3WM_mapInProgress, $wgArticle, $TI3WM_mapArgs;
	global $wgOut, $action;
	
	// Don't want robots following the link to TI3WM_ShowSingleTile and using up cpu/	
	$wgOut->setRobotpolicy( 'nofollow' );

	// Figure out exactly what arguments were specified for this map
	$TI3WM_mapArgs = array_diff_assoc($args, $TI3WM_defaultArgs);

	$oldDefaultArgs = $TI3WM_defaultArgs;
	$TI3WM_defaultArgs = $args;
	$TI3WM_mapInProgress = true;
	
	// Cache local arguments
	$ThisPage = $parser->getTitle()->getPartialURL();
//	if(!isset($args['page']))
//		$Page = $ThisPage;
//	else $Page = $args['page'];
	
	$Width = $args['width'];
	$Height = $args['height'];
	$ShowNumbers = $args['shownumbers'];
	$Scale = $args['scale'];
	$Format = $args['format'];
	$SaveMap = $args['savemap'];
	$TileExistsXYLinks = $args['tileexistsxylinks'];
	$TileMissingXYLinks = $args['tilemissingxylinks'];
	$TileImageLinks = $args['tileimagelinks'];
	$Output = $args['output'];
	if($Output == 'html')
		$endl = "\n";
		
	if(!$ShowNumbers)
	{
		$TileExistsXYLinks = 0;
		$TileMissingXYLinks = 0;
	}
		
	if($SaveMap)
	{
		$TileExistsXYLinks = 0;
		$TileMissingXYLinks = 0;
		$TileImageLinks = 0;
	}
	
	if(!isset($args['page']) || $args['page'] == $ThisPage)
	{
		$Page = $ThisPage;
		
		if(isset($_GET['oldid']))
		{
			$r = Revision::newFromId($_GET['oldid']);
			$p = TI3WM_ParsePageFromText($r->getId(), $r->getText(), &$parser);
		}
		else
		{
			//$r = Revision::newFromId($parser->mRevisionId);
			$p = TI3WM_ParsePage($Page, &$parser);			
		}

	}
	else
	{
		$Page = $args['page'];
		$p = TI3WM_ParsePage($Page, &$parser);
	}
	
	if($p !== false)
	{
		if(!isset($TI3WM_mapArgs['width']))
			$Width = $p[0];
		if(!isset($TI3WM_mapArgs['height']))
			$Height = $p[1];
		$tiles = $p[2];
	}

	// Figure out exactly what arguments were specified for this map
	$args = array_diff_assoc($args, $oldDefaultArgs);
	
	// Remerge args in case globals were changed in parsing the map
	$args = array_merge($TI3WM_defaultArgs, $args);
	
	$PieceScale = $args['piecescale'];
	$VoidTile = $args['void'];

	$voidTileHTML = $parser->recursiveTagParse("<tile tile=$VoidTile/>");
	
	// _TI3WikiMap
	if($Width > 17)
		$Width = 17;
	if($Height > 17)
		$Height = 17;
	
	$ScaledWidthPX = 432 * $Scale;
	$ScaledHeightPX = 376 * $Scale;
	$TotalWidthPX = $Width * $ScaledWidthPX;
	$TotalHeightPX = ($Height+0.5) * $ScaledHeightPX;
					
	$retv .= "<div style=\"position: relative; width: ${TotalWidthPX}px; height: ${TotalHeightPX}px;\">$endl";
	for($x=0; $x<$Width; $x++)
	{
		for($y=0; $y<$Height;$y++)
		{
			$PX = $x * 324 * $Scale;
			$PY = (($y * 376) + (($x % 2 > 0) ? 0 : 188)) * $Scale;
		
			if(isset($tiles[$x][$y]))
			{
				$Section = $tiles[$x][$y][0];
				$Content = $tiles[$x][$y][1];
				if(strlen($Content) == 0)
					$Content = $voidTileHTML;
				else $Content = $parser->recursiveTagParse($Content);

					if($TileExistsXYLinks)
						$Content .= TI3WM_LabelHTML(	"$x-$y"
														,30 // Font
														,0 // Width
														,0 // Height
														,'center' // Align
														,284 // Left
														,10 // Top
														,false // Center
														,10 // Z
														,1 // PieceScale
														,$Scale
														,$args
												);
			}
			else
			{
				$Section = "$x-$y";
				$Content =  $voidTileHTML;
				if($TileMissingXYLinks)
				{
					$Content .= TI3WM_LabelHTML(	$Section
													,30 // Font
													,0 // Width
													,0 // Height
													,'center' // Align
													,284 // Left
													,10 // Top
													,false // Center
													,10 // Z
													,1 // PieceScale
													,$Scale
													,$args
												);
				}
			}
			
			if($TileImageLinks)
				$Content = "<a class=tileanchor href=\"${TI3WM_indexURL}TI3WM_ShowSingleTile&page=$Page&x=$x&y=$y&format=$Format&piecescale=$PieceScale&return=$ThisPage&void=$VoidTile\">$Content</a>";
				
			$tileHTML = "<div style=\"position: absolute; left: ${PX}px; top: ${PY}px; width: ${ScaledWidthPX}px; height: ${ScaledHeightPX};\">$endl";
			$tileHTML .= $Content;
			$tileHTML .= "</div>$endl";

				
			$retv .= $tileHTML;
		}
	}
	
	$retv .= "</div>$endl";
	
	$TI3WM_defaultArgs = $oldDefaultArgs;
	$TI3WM_mapInProgress = false;
	
	return $retv;
}

function TI3WM_ShowSingleTile($content, $args, &$parser )
{
	global $TI3WM_defaultArgs, $TI3WM_indexURL, $TI3WM_mapInProgress, $TI3WM_mapArgs;
	
	// Figure out exactly what arguments were specified for this map
	$TI3WM_mapArgs = array_diff_assoc($args, $TI3WM_defaultArgs);
	
	$TI3WM_mapInProgress = true;
	$oldDefaultArgs = $TI3WM_defaultArgs;
	
	$parser->disableCache();
	
	if(!isset($_GET['page']))
		return "TI3WikiMap2: page must be set in URL<br/>";
	$Page = $_GET['page'];

	$x = isset($_GET['x']) || !is_numeric($_GET['x']) ? $_GET['x'] : 0;
	$y = isset($_GET['y']) || !is_numeric($_GET['y']) ? $_GET['y'] : 0;
	
	$Scale = isset($_GET['scale']) && is_numeric($_GET['scale']) ? $_GET['scale'] : 1;
	$Format = isset($_GET['format']) && ($_GET['format'] == 'gif' || $_GET['format']== 'png') ? $_GET['format'] : 'gif';
	$PieceScale = isset($_GET['piecescale']) && is_numeric($_GET['piecescale']) ? $_GET['piecescale'] : 1;
	$VoidTile = isset($_GET['void']) ? $_GET['void'] : $TI3WM_defaultArgs['void'];
	$ReturnPage = $_GET['return'];
	
	if(strlen($ReturnPage) > 0)
	{
		$title = Title::newFromText( $ReturnPage );
		if(!is_object( $title ) || !$title->exists())
			return "TI3WikiMap2: $ReturnPage not found<br/>";
	}
	

	$p = TI3WM_ParsePage($Page, &$parser);
	if($p === false)
		return "TI3WikiMap2: $page not found<br/>";
	
	$tiles = $p[2];
	if(isset($tiles[$x][$y]))
	{
		$Section = $tiles[$x][$y][0];
		$Content = $tiles[$x][$y][1];
		$section_num = $tiles[$x][$y][2];;
		
		if(strlen($Content) == 0)
			$Content = "<tile tile=$VoidTile/>";
			
		$editLink = "$TI3WM_indexURL$Page&action=edit&section=$section_num";
		$pageLink = "$TI3WM_indexURL$Page#$Section";
	}
	else
	{
		$Section = "$x-$y";
		$Content = "<tile tile=$VoidTile/>";
		$editLink = "$TI3WM_indexURL$Page&action=edit";
		$pageLink = "$TI3WM_indexURL$Page";
	}
	
	$oldDefaultArgs = $TI3WM_defaultArgs;
	
	$TI3WM_defaultArgs['scale'] = $Scale;
	$TI3WM_defaultArgs['format'] = $Format;
	$TI3WM_defaultArgs['piecescale'] = $PieceScale;
	
	$tileHTML = $parser->recursiveTagParse($Content);
	
	$tileHTML = "<a class=tileanchor href=\"$editLink\">$tileHTML</a>";

	$retv = "<table class=ti3pbem>";
	$retv .= "<tr><td><a class=tileanchor href=\"$pageLink\">$ReturnPage: $x-$y</a></td></tr>";
	$retv .= "<tr><td><br/><div style=\"position: relative\">$tileHTML</div><br/></td></tr>";
	$retv .= "<tr><td align=center><b>Left click tile to edit map data.</b><br/>To return to main map, use browser back button or click <a href=\"$TI3WM_indexURL$ReturnPage\">here</a></td></tr>";
	$retv .= "</table>";
	
	$TI3WM_defaultArgs = $oldDefaultArgs;
	$TI3WM_mapInProgress = false;
	
	return $retv;
}

?>