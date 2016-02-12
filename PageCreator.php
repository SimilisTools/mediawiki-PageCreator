<?php
 

define( 'PPP_PAGECREATOR', 'PAGECREATOR' );
define( 'PPP_CREATIONTIMESTAMP', 'CREATIONTIMESTAMP' );

$GLOBALS['wgHooks']['LanguageGetMagic'][] = 'wfPppWikiWords';
$GLOBALS['wgHooks']['MagicWordwgVariableIDs'][] = 'wfPppDeclareVarIds';
$GLOBALS['wgHooks']['ParserGetVariableValueSwitch'][] = 'wfPppAssignAValue';

$GLOBALS['wgExtensionCredits']['variable'][] = array(
        'name' => 'PageCreator',
        'author' => 'Pierro78 (sql query from PLX)',
        'version' => '0.3',
        'description' => 'Get Page Creator in variable PAGECREATOR, Creation TimeStamp in CREATIONTIMESTAMP',
        'url' => 'http://www.mediawiki.org/wiki/Extension:PageCreator',
);
 

function wfPppWikiWords( &$magicWords, $langCode ) {
	// tell MediaWiki that all {{NiftyVar}}, {{NIFTYVAR}}, {{CoolVar}},
	// {{COOLVAR}} and all case variants found in wiki text should be mapped to
	// magic ID 'mycustomvar1' (0 means case-insensitive)
	$magicWords[PPP_PAGECREATOR] = array( 0, PPP_PAGECREATOR);
	$magicWords[PPP_CREATIONTIMESTAMP] = array( 0, PPP_CREATIONTIMESTAMP);
	
	// must do this or you will silence every LanguageGetMagic hook after this!
	return true;
}

function wfPppAssignAValue( &$parser, &$cache, &$magicWordId, &$ret ) {
	
	if ( PPP_PAGECREATOR == $magicWordId ) {
		global $wgUser;
		$revuser = $wgUser->getName();

		$ret = $revuser;

		global $wgArticle;

		if (isset($wgArticle)){
		  $myArticle=$wgArticle;
		}
		else{ 
		  $myTitle=$parser->getTitle();
		  $myArticle=new Article($myTitle);
		}

		$dbr = wfGetDB( DB_SLAVE );
		$revTable = $dbr->tableName( 'revision' );

		$pageId = $myArticle->getId();
		$q0 = "select rev_user_text from ".$revTable." where rev_page=".$pageId." order by rev_timestamp asc limit 1";
		if(($res0 = mysql_query($q0)) && ($row0 = mysql_fetch_object($res0))) {
		  $ret=$row0->rev_user_text;
		}
		else{

		  $myTitle=$parser->getTitle();
		  $articleId=$myTitle->getArticleID();

		}
	}

	if ( PPP_CREATIONTIMESTAMP == $magicWordId ) {
			global $wgUser;
			$revuser = $wgUser->getName();

		    $ret = $revuser;

		    global $wgArticle;

		    if (isset($wgArticle)) {
				$myArticle=$wgArticle;
		    }
		    else { 
				$myTitle=$parser->getTitle();
				$myArticle=new Article($myTitle);
		    }

		    $dbr = wfGetDB( DB_SLAVE );
		    $revTable = $dbr->tableName( 'revision' );

		    $pageId = $myArticle->getId();
		    $q0 = "select rev_timestamp from ".$revTable." where rev_page=".$pageId." order by rev_timestamp asc limit 1";
		    if(($res0 = mysql_query($q0)) && ($row0 = mysql_fetch_object($res0))) {
				$ret=$row0->rev_timestamp;
		    }
		    else {
				$myTitle=$parser->getTitle();
				$articleId=$myTitle->getArticleID();
		    }
	}

	// We must return true for two separate reasons:
	// 1. To permit further callbacks to run for this hook.
	//    They might override our value but that's life.
	//    Returning false would prevent these future callbacks from running.
	// 2. At the same time, "true" indicates we found a value.
	//    Returning false would the set variable value to null.
	//
	// In other words, true means "we found a value AND other
	// callbacks will run," and false means "we didn't find a value
	// AND abort future callbacks." It's a shame these two meanings
	// are mixed in the same return value.  So as a rule, return
	// true whether we found a value or not.
	return true;
}

function wfPppDeclareVarIds( &$customVariableIds ) {
	// $customVariableIds is where MediaWiki wants to store its list of custom
	// variable IDs. We oblige by adding ours:
	$customVariableIds[] = PPP_PAGECREATOR;
	$customVariableIds[] = PPP_CREATIONTIMESTAMP;

	// must do this or you will silence every MagicWordwgVariableIds hook
	// registered after this!
	return true;
}

