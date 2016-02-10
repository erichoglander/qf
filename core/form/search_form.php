<?php
class Search_Form_Core extends Form {
  
  protected function structure() {
    return [
      "name" => "search",
      "items" => [
        "q" => [
          "type" => "search",
          "value" => $this->get("q"),
          "attributes" => [
            "placeholder" => t("Search..."),
          ],
          "filter" => ["strip_tags", "trim"],
        ],
        "submit" => [
          "type" => "submit",
          "value" => t("Search"),
        ],
      ],
    ];
  }

}