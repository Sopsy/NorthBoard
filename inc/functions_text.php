<?php
// Northpole.fi
// Tekstinkäsittely
// 16.2.2010

function bbcode_format($str) {

		$str = str_replace("javascript:", "", $str);

		$simple_search = array(
				'/\[b\](.*?)\[\/b\]/is',
				'/\[i\](.*?)\[\/i\]/is',
				'/\[u\](.*?)\[\/u\]/is',
				'/\[s\](.*?)\[\/s\]/is',
				'/\[align\=(left|center|right)\](.*?)\[\/align\]/is',
				'/\[mail\=(.*?)\](.*?)\[\/mail\]/is',
				'/\[mail\](.*?)\[\/mail\]/is',
				'/\[url\=www\.(.*?)\](.*?)\[\/url\]/is',
				'/\[url\]www\.(.*?)\[\/url\]/is',
				'/\[url\=(.*?)\](.*?)\[\/url\]/is',
				'/\[url\](.*?)\[\/url\]/is',
				'/\[spoiler\](.*?)\[\/spoiler\]/is',
				'/\[code\](.*?)\[\/code\]/is',
				'/\[quote\]([^\[]+)\[\/quote\]/is',
				'/\[quote\=([^\]]+)\]([^\[]+)\[\/quote\]/is'
				);
				//'/\[color\=(.*?)\](.*?)\[\/color\]/is',

		$simple_replace = array(
				'<strong>$1</strong>',
				'<em>$1</em>',
				'<u>$1</u>',
				'<span style="text-decoration:line-through;">$1</span>',
				'</p><div style="text-align: $1;"><p>$2</p></div><p>',
				'<a href="mailto:$1">$2</a>',
				'<a href="mailto:$1">$1</a>',
				'<a href="http://www.$1">$2</a>',
				'<a href="http://www.$1">$1</a>',
				'<a href="$1">$2</a>',
				'<a href="$1">$1</a>',
				'<span class="spoiler">$1</span>',
				'</p><pre>$1</pre><p>',
				'</p><div class="bbquote"><p>$1</p></div><p>',
				'</p><div class="bbquote"><p>$2<span>&mdash; $1</span></p></div><p>'
				);
				//'<span style="color: $1;">$2</span>',
				
		// Do simple BBCode's
		$str = preg_replace($simple_search, $simple_replace, $str);

		return $str;
}

// Thanks goes to Koukari!
function regulateWords( $data, $max_length = 120 )
{
	if( $max_length <= 0 )
		return $data;

	$length = strlen( $data );
	$pos = 0;			// Current position
	$tag = false;		// Are we inside a tag atm?
	$wordLength = 0;	// The length of the current word

	while( $pos < $length )
	{
		if( $tag == true )
		{
			if( $data[$pos] == '>' )
				$tag = false;
		}

		else
		{
			switch( $data[$pos] )
			{
				case '<':
					$tag = true;
					break;

				case ' ':
					$wordLength = 0;
					break;

				case "\n":
					$wordLength = 0;
					break;

				default:
					$wordLength++;
					break;
			}

			if( $wordLength > $max_length )
			{
				$sub1 = substr( $data, 0, $pos );
				$sub2 = substr( $data, $pos );
				$data = $sub1.' '.$sub2;
				$wordLength = 0;
				$length++;
			}
		}
		$pos++;
	}
	return $data;
}

function removeForbiddenUnicode($text)
{
	// Remove invisible characters and characters that mess up the formatting.
	$unicode = array(
		'/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/',	// Unicode control characters
		'/'. pack("cc", 0xC2, 0xAD) .'/',			// 'SOFT HYPHEN' (U+00AD)
		'/'. pack("ccc", 0xE1, 0x85, 0x9F) .'/',	// 'HANGUL CHOSEONG FILLER' (U+115F)
		'/'. pack("ccc", 0xE2, 0x80, 0x8B) .'/',	// 'ZERO WIDTH SPACE' (U+200B)
		'/'. pack("ccc", 0xE2, 0x80, 0x8C) .'/',	// 'ZERO WIDTH NON-JOINER' (U+200C)
		'/'. pack("ccc", 0xE2, 0x80, 0x8D) .'/',	// 'ZERO WIDTH JOINER' (U+200D)
		'/'. pack("ccc", 0xE2, 0x80, 0x8E) .'/',	// 'LEFT-TO-RIGHT MARK' (U+200E)
		'/'. pack("ccc", 0xE2, 0x80, 0x8F) .'/',	// 'RIGHT-TO-LEFT MARK' (U+200F)
		'/'. pack("ccc", 0xE2, 0x80, 0xA8) .'/',	// 'LINE SEPARATOR' (U+2028)
		'/'. pack("ccc", 0xE2, 0x80, 0xA9) .'/',	// 'PARAGRAPH SEPARATOR' (U+2029)
		'/'. pack("ccc", 0xE2, 0x80, 0xAA) .'/',	// 'LEFT-TO-RIGHT EMBEDDING' (U+202A)
		'/'. pack("ccc", 0xE2, 0x80, 0xAB) .'/',	// 'RIGHT-TO-LEFT EMBEDDING' (U+202B)
		'/'. pack("ccc", 0xE2, 0x80, 0xAE) .'/',	// 'RIGHT-TO-LEFT OVERRIDE' (U+202E)
		'/'. pack("ccc", 0xE2, 0x81, 0xA0) .'/',	// 'WORD JOINER' (U+2060)
		'/'. pack("ccc", 0xE2, 0x81, 0xA1) .'/',	// 'FUNCTION APPLICATION' (U+2061)
		'/'. pack("ccc", 0xE2, 0x81, 0xA2) .'/',	// 'INVISIBLE TIMES' (U+2062)
		'/'. pack("ccc", 0xE2, 0x81, 0xA3) .'/',	// 'INVISIBLE SEPARATOR' (U+2063)
		'/'. pack("ccc", 0xE2, 0x81, 0xA4) .'/',	// 'INVISIBLE PLUS' (U+2064)
		'/'. pack("ccc", 0xEF, 0xB8, 0x80) .'/',	// 'VARIATION SELECTOR-1' (U+FE00)
		'/'. pack("ccc", 0xEF, 0xB8, 0x81) .'/',	// 'VARIATION SELECTOR-2' (U+FE01)
		'/'. pack("ccc", 0xEF, 0xB8, 0x82) .'/',	// 'VARIATION SELECTOR-3' (U+FE02)
		'/'. pack("ccc", 0xEF, 0xB8, 0x83) .'/',	// 'VARIATION SELECTOR-4' (U+FE03)
		'/'. pack("ccc", 0xEF, 0xB8, 0x84) .'/',	// 'VARIATION SELECTOR-5' (U+FE04)
		'/'. pack("ccc", 0xEF, 0xB8, 0x85) .'/',	// 'VARIATION SELECTOR-6' (U+FE05)
		'/'. pack("ccc", 0xEF, 0xB8, 0x86) .'/',	// 'VARIATION SELECTOR-7' (U+FE06)
		'/'. pack("ccc", 0xEF, 0xB8, 0x87) .'/',	// 'VARIATION SELECTOR-8' (U+FE07)
		'/'. pack("ccc", 0xEF, 0xB8, 0x88) .'/',	// 'VARIATION SELECTOR-9' (U+FE08)
		'/'. pack("ccc", 0xEF, 0xB8, 0x89) .'/',	// 'VARIATION SELECTOR-10' (U+FE09)
		'/'. pack("ccc", 0xEF, 0xB8, 0x8A) .'/',	// 'VARIATION SELECTOR-11' (U+FE0A)
		'/'. pack("ccc", 0xEF, 0xB8, 0x8B) .'/',	// 'VARIATION SELECTOR-12' (U+FE0B)
		'/'. pack("ccc", 0xEF, 0xB8, 0x8C) .'/',	// 'VARIATION SELECTOR-13' (U+FE0C)
		'/'. pack("ccc", 0xEF, 0xB8, 0x8D) .'/',	// 'VARIATION SELECTOR-14' (U+FE0D)
		'/'. pack("ccc", 0xEF, 0xB8, 0x8E) .'/',	// 'VARIATION SELECTOR-15' (U+FE0E)
		'/'. pack("ccc", 0xEF, 0xB8, 0x8F) .'/',	// 'VARIATION SELECTOR-16' (U+FE0F)
		'/'. pack("ccc", 0xEF, 0xBB, 0xBF) .'/',	// 'ZERO WIDTH NO-BREAK SPACE' (U+FEFF)
	);
	$text = preg_replace($unicode, '', $text);
	
	return $text;
}

// Tekstin muotoilua
function format_text($text) {
	global $cfg;
	
	// Karsitaan kautta... keno.. mitkälie, ja hienosäädetään
	// $text = str_replace('&amp;', '&', htmlspecialchars($text));
	$text = htmlspecialchars($text);
	
	// Remove unicode control characters
	$text = removeForbiddenUnicode($text);
	
        // Automaattinen korvaus ***://, www., mailto:, ftp. teksteille linkeiksi! V2!
	// Still not working correctly!
	// http://northpole.fi/northboard/1277755/
	// http://northpole.fi/northboard/1277848/
        //$text = preg_replace("/(([\da-z\.-]+):\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([#\?%\~\;\=&\/\w \.-]*)*\/?/", "<a href=\"\\0\" rel=\"nofollow\" onclick=\"window.open(this.href); return false;\">\\0</a>", $text);
     	$text = preg_replace("#(^|[\n \>\(])([\w]+?://[\w]+[^\) \"\n\r\t<]*)#ise", "'\\1<a href=\"\\2\" rel=\"nofollow\" onclick=\"window.open(this.href); return false;\">\\2</a>'", $text);
	$text = preg_replace("#(^|[\n \>\(])((www)\.[^\) \"\t\n\r<]*)#ise", "'\\1<a href=\"http://\\2\" rel=\"nofollow\" onclick=\"window.open(this.href); return false;\">\\2</a>'", $text);
	$text = preg_replace("#(^|[\n \>\(])((ftp)\.[^\) \"\t\n\r<]*)#ise", "'\\1<a href=\"ftp://\\2\" rel=\"nofollow\" onclick=\"window.open(this.href); return false;\">\\2</a>'", $text);
	$text = preg_replace("#(^|[\n \>\(])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\)\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $text);
        
        // viestilinkit ja vihertekstit
	$text = preg_replace("#(&gt;&gt;)([0-9]+)#ise", "'<a href=\"http://". $cfg['htmldir_plain'] ."/redirect/\\2\" title=\"ajax;http://". $cfg['htmldir_plain'] ."/scripts/ajax/message.php?id=\\2\" onclick=\"highlight_post(\'\\2\', this); return false;\">\\1\\2</a>'", $text);
	$text = preg_replace("#(^|[\n\]])(&gt;)([^\n\r]+)#ise", "'\\1<span class=\"quote\">\\2\\3</span>'", $text);	
	
	// Ja tähän BBkoodisysteemi..
	$text = bbcode_format($text);
	

	// Karsitaan liiat rivinvaihdot
	$text = preg_replace('/(\n|(\r\n)){11,}/', "\n\n\n\n\n\n\n\n\n\n", $text);
	
	$text = trim($text);
	$text = regulateWords($text, $cfg['max_word_length']);
        
	// Ja viimeisenä vielä poistetaan MySQL-koodi tekstistä
	$text = mysql_real_escape_string($text);

	return $text;
}

// Tekstin lyhentäminen
function truncate($text, $messageid, $length = 128, $link = false, $truncated_text = true) {

	if(!empty($text)) {
		
		if($link) {
			$a = '<a href="'. $link .'" onclick="unTruncate('. $messageid .'); return false;">';
			$b = '</a>';
			$c = T_("here");
		}
		else {
			$a = '';
			$b = '';
			$c = T_('"Reply"');
		}
		
		$array = explode("\n", $text);
		
		if( count($array) > 15 )
		{
			$text = limitLines($text, 15);
		}

		// Don't count html tags
		$curlength = preg_replace("/<\/?\w+((\s+(\w|\w[\w-]*\w)(\s*=\s*(?:\".*?\"|'.*?'|[^'\">\s]+))?)+\s*|\s*)\/?>/i", "", $text);
		$curlength = mb_strlen($curlength);
		
		if($curlength > $length)
			$text = mb_substrws($text, $length);
		
		/*
		$opened = array();
		// loop through opened and closed tags in order
		if(preg_match_all("/<(\/?[a-z]+)>?/i", $text, $matches)) {
			foreach($matches[1] as $tag) {
				if(preg_match_all('/\"([^\"]*)\"/i', $text, $match)) {
					$open_quotes = true;
				}
				else $open_quotes = false;
				if(preg_match("/^[a-z]+$/i", $tag, $regs)) {
					// a tag has been opened
					if(strtolower($regs[0]) != 'br')
						$opened[] = $regs[0];
				}
				elseif(preg_match("/^\/([a-z]+)$/i", $tag, $regs)) {
					// a tag has been closed
					unset($opened[array_pop(array_keys($opened, $regs[1]))]);
				}
			}
		}
		
		foreach($opened AS $tag)
		{
			$text .= ($open_quotes ? '">' : '') . '</'. $tag .'>';
		}
		*/
		if($curlength > $length)
			$text .= '...';
			

		if($curlength > $length OR count($array) > 15)
			$text .= ($truncated_text ? '<span class="msg_cut" id="msg_cut_'. $messageid .'"><br /><br />'. $a . sprintf(T_("This message was truncated, click %s to see the rest of it."), $c) . $b .'</span>' : '');
		
		return $text;
	}
	else return false;
}

// http://php.net/manual/en/function.nl2br.php
function nl2br_pre($string) {
    // First, check for <pre> tag
    if(strpos($string, "<pre>") === false)
    {
        return nl2br($string);
    }

    // If there is a <pre>, we have to split by line
    // and manually replace the linebreaks with <br />
    $strArr=explode("\n", $string);
    $output="";
    $preFound=3;

    // Loop over each line
    foreach($strArr as $line)
    {    // See if the line has a <pre>. If it does, set $preFound to true
        if(strpos($line, "<pre>") === true)
        {
            $preFound=1;
        }
        elseif(strpos($line, "</pre>"))
        {
            $preFound=2;
        }

        // If we are in a pre tag, just give a \n, else a <br />
        switch($preFound) {
            case 1: // found a <pre> tag, close the <p> element
                $output .= "</p>\r\n" . $line . "\r\n";
                break;
            case 2: // found the closing </pre> tag, append a newline and open a new <p> element
                $output .= $line . "\r\n<p>";
                $preFound = 3; // switch to normal behaviour
                break;
            case 3: // simply append a <br /> element
                $output .= $line . "<br />";
                break;
        }
    }

    return $output;
}

function clean_pre($text) {
  $text = str_replace(array("<br />", "<br>", "<br/>"), ".", $text);
  return $text;
}

/**
* word-sensitive substring function with html tags awareness
* @param text The text to cut
* @param len The maximum length of the cut string
* @returns string
**/
// Oooh yeah, a fixed function from some bugtracker describing another issue, lol!
// http://expressionengine.com/bug_tracker/bug/10488/
function mb_substrws($text, $len = 160) {
    if( (mb_strlen($text) > $len) ) {

        $whitespaceposition = mb_strpos($text," ",$len)-1;

        if( $whitespaceposition > 0 ) {
            $chars = count_chars(mb_substr($text, 0, ($whitespaceposition+1)), 1);
/*
			echo '<!--
			';
			echo $text;
			print_r($chars);
			echo '-->';
*/
            if ( ( !empty( $chars[ord('<')] ) AND !empty( $chars[ord('>')] ) AND $chars[ord('<')] > $chars[ord('>')] ) OR ( !empty( $chars[ord('<')] ) AND empty( $chars[ord('>')] ) ) )
			{
                $whitespaceposition = mb_strpos($text,">",$whitespaceposition)-1;
			}
            $text = mb_substr($text, 0, ($whitespaceposition+1));
        }

		$text = closeUnclosedTags($text);

    }
    return $text;
}

function limitLines($text, $lines)
{
	// Limit the amount of lines
	$array = explode("\n", $text);
	$text = array();
	for( $i = 0; $i < $lines; $i++ )
	{
		if($i >= count($array)) break;
		$text[] = $array[$i];
	}
	$text = implode("\n", $text);

	$text = closeUnclosedTags($text);

	return $text;
}

function closeUnclosedTags($text)
{
	$patt_open = "%((?<!</)(?<=<)[\s]*[^/!>\s]+(?=>|[\s]+[^>]*[^/]>)(?!/>))%";
	$patt_close = "%((?<=</)([^>]+)(?=>))%";
	if (preg_match_all($patt_open,$text,$matches))
	{
		$m_open = $matches[1];
		if(!empty($m_open))
		{
			preg_match_all($patt_close,$text,$matches2);
			$m_close = $matches2[1];
			if (count($m_open) > count($m_close))
			{
				$c_tags = array();
				$m_open = array_reverse($m_open);
				foreach ($m_close as $tag)
				{
					if(empty($c_tags[$tag]))
						$c_tags[$tag] = 0;
					$c_tags[$tag]++;
				}
				foreach ($m_open as $k => $tag) if ( !empty( $c_tags[$tag] ) AND $c_tags[$tag]-- <= 0 ) $text.='</'.$tag.'>';
			}
		}
	}
	return $text;
}

?>
