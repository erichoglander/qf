<?php
namespace JsonToHtml {
  
  function htmlToJson($html) {
    $json = [];
    $stack = [];
    $i = -1;
    $singleTags = ["input", "img", "br", "hr", "link", "meta", "area", "base", "col", "frame", "param", "isindex", "basefont", "path"];
    while (true) {
      $x = strpos($html, "<");
      // Comment
      if (strpos($html, "<!--") === 0) {
        $x = strpos($html, "-->");
        $value = [
          "comment" => substr($html, 4, $x-4),
        ];
        if ($i == -1)
          $json[] = $value;
        else
          $stack[$i]["children"][] = $value;
        $html = substr($html, $x+3);
      }
      // Element
      else if ($x === 0) {
        $x = strpos($html, ">");
        if ($x === false)
          return null;
        $tag = substr($html, 0, $x+1);
        // End of tag
        if ($tag[1] == "/") {
          $el = array_pop($stack);
          $i--;
          if ($i == -1) {
            $json[] = $el;
          }
          else {
            $stack[$i]["children"][] = $el;
          }
        }
        // New tag
        else {
          $stack[++$i] = [
            "tagName" => preg_replace("/^\<\s*([a-z0-9]+).*$/si", "$1", $tag),
          ];
          $len = strlen($tag);
          $tlen = strlen($stack[$i]["tagName"]);
          if ($len-2 > $tlen) {
            $stack[$i]["attributes"] = [];
            $key = $value = null;
            $where = "key";
            $add = false;
            $q = null;
            for ($cx=$tlen+2; $cx<$len; $cx++) {
              $c = $tag[$cx];
              if ($q && $c == $q) {
                $where = "key";
                $add = true;
                $q = null;
              }
              else if (!$q && ($c == "'" || $c == '"')) {
                $q = $c;
              }
              else if ($c == "=" && !$q) {
                $where = "value";
              }
              else if (in_array($c, [" ", ">", "\n", "\r"]) && !$q) {
                $where = "key";
                $add = true;
              }
              else if ($c == "/" && $where == "key") {
                continue;
              }
              else {
                if ($where == "key")
                  $key.= $c;
                else
                  $value.= $c;
              }
              if ($add) {
                if ($key)
                  $stack[$i]["attributes"][$key] = html_entity_decode($value, ENT_QUOTES);
                $add = false;
                $key = $value = "";
              }
            }
          }
          if (substr($tag, -2) == "/>" || in_array($stack[$i]["tagName"], $singleTags)) {
            $el = array_pop($stack);
            $i--;
            if ($i == -1) {
              $json[] = $el;
            }
            else {
              $stack[$i]["children"][] = $el;
            }
          }
        }
        $html = substr($html, $x+1);
      }
      else if ($x === false) {
        $value = str_replace('"', '&quot;', $html);
        if ($i == -1)
          $json[] = $value;
        else
          $stack[$i]["children"][] = $value;
        break;
      }
      else {
        $value = str_replace('"', '&quot;', substr($html, 0, $x));
        if ($i == -1)
          $json[] = $value;
        else
          $stack[$i]["children"][] = $value;
        $html = substr($html, $x);
      }
    }
    return $json;
  }

}