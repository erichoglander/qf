<?php
class LogListSearch_Form extends Form {
  
  protected function structure() {
    
    // Fetch categories
    $categories = [];
    $rows = $this->Db->getRows("
        SELECT DISTINCT(category)
        FROM `log`
        ORDER BY category ASC");
    foreach ($rows as $row)
      $categories[$row->category] = $row->category;
    
    $form = [
      "name" => "log_list_search",
      "attributes" => [
        "class" => "form-list-search",
      ],
      "items" => [
        "q" => [
          "type" => "search",
          "attributes" => [
            "placeholder" => t("Search..."),
          ],
          "value" => $this->get("q"),
        ],
        "type" => [
          "type" => "select",
          "empty_option" => t("- Type -"),
          "options" => [
            "info" => t("Info"),
            "success" => t("Success"),
            "warning" => t("Warning"),
            "error" => t("Error"),
          ],
          "value" => $this->get("type"),
        ],
        "category" => [
          "type" => "select",
          "empty_option" => t("- Category -"),
          "options" => $categories,
          "value" => $this->get("category"),
        ],
        "submit" => [
          "type" => "submit",
          "value" => t("Search"),
        ],
      ],
    ];
    return $form;
  }
  
}