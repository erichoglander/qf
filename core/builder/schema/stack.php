<?php
class Stack extends Schema {
  
  public static function input($args) {
    $arr = [
      [
        "key" => "name",
        "prompt" => "Entity class name",
      ],
      [
        "key" => "singluar",
        "prompt" => "Singular name of entity",
      ],
      [
        "key" => "plural",
        "prompt" => "Plural name of entity",
      ],
    ];
    $i = count($args);
    $n = count($arr);
    if ($i < $n)
      return $arr[$i];
    return null;
  }
  
  public static function mods($args) {
    extract($args);
    $path = DOC_ROOT."/extend/class/acl.php";
    
    print "Modifying acl... ";
    Builder::mkdir($path);
    
    $new_code = <<<EOD

  protected function {$camel_lower}AdminAccess(\$User) {
    return \$User->id() == 1;
  }
EOD;
    $new_lines = explode("\n", $new_code);
    
    if (file_exists($path)) {
      $code = file_get_contents($path);
      if (strpos($code, "function ".$camel_lower."AdminAccess") !== false) {
        print "Method exists\n";
        return;
      }
    }
    else {
      $code = <<<EOD
<?php
class Acl extends Acl_Core {
  
}
EOD;
    }
    
    $lines = explode("\n", $code);
    for ($i = count($lines)-1; $i>=0; $i--) {
      if (strpos($lines[$i], "}") !== false)
        break;
    }
    if ($i == 0) {
      print "Failed, corrupt file\n";
      return;
    }
    
    $lines = array_merge(
      array_slice($lines, 0, $i-1),
      $new_lines,
      array_slice($lines, $i-1)
    );
    $code = implode("\n", $lines);
    if (!@file_put_contents($path, $code)) {
      print "Failed, couldn't write to file\n";
      return;
    }
    
    print "OK\n";
  }
  
  public static function parseArgs(&$args) {
    $args["name"] = ucwords($args["name"]);
    $args["snake"] = Builder::camelToSnake($args["name"]);
    $args["camel_lower"] = strtolower($args["name"][0]).substr($args["name"], 1);
    $args["uri"] = str_replace("_", "-", $args["snake"]);
    $args["singular_lower"] = strtolower($args["singular"]);
    $args["plural_lower"] = strtolower($args["plural"]);
  }
  
  public static function files(&$args) {
    static::parseArgs($args);
    return [
      "controller" => [
        "path" => "controller/".$args["snake"]."_controller.php",
        "content" => static::fileController($args),
      ],
      "model" => [
        "path" => "model/".$args["snake"]."_model.php",
        "content" => static::fileModel($args),
      ],
      "edit_form" => [
        "path" => "form/".$args["snake"]."_edit_form.php",
        "content" => static::fileEditForm($args),
      ],
      "entity" => [
        "path" => "entity/".$args["snake"]."_entity.php",
        "content" => static::fileEntity($args),
      ],
      "view_add" => [
        "path" => "view/".$args["snake"]."/add.php",
        "content" => static::fileViewAdd($args),
      ],
      "view_edit" => [
        "path" => "view/".$args["snake"]."/edit.php",
        "content" => static::fileViewEdit($args),
      ],
      "view_delete" => [
        "path" => "view/".$args["snake"]."/delete.php",
        "content" => static::fileViewDelete($args),
      ],
      "view_list" => [
        "path" => "view/".$args["snake"]."/list.php",
        "content" => static::fileViewList($args),
      ],
    ];
  }
  
  public static function fileController($args) {
    extract($args);
    return <<<EOD
<?php
class {$name}_Controller extends Controller {
  
  public function acl(\$action, \$args = []) {
    return ["{$camel_lower}Admin"];
  }
  
  public function addAction() {
    \$Form = \$this->getForm("{$name}Edit");
    if (\$Form->isSubmitted()) {
      try {
        \$this->Model->add{$name}(\$Form->values());
        setmsg(t("{$singular} added"), "success");
        redirect("{$uri}/list");
      }
      catch (Exception \$e) {
        setmsg(\$e->getMessage(), "error");
      }
    }
    \$this->viewData["form"] = \$Form->render();
    return \$this->view("add");
  }
  
  public function editAction(\$args = []) {
    if (empty(\$args[0]))
      return \$this->notFound();
    \${$name} = \$this->getEntity("{$name}", \$args[0]);
    if (!\${$name}->id())
      return \$this->notFound();
    \$Form = \$this->getForm("{$name}Edit", ["{$name}" => \${$name}]);
    if (\$Form->isSubmitted()) {
      try {
        \$this->Model->edit{$name}(\${$name}, \$Form->values());
        setmsg(t("{$singular} saved"), "success");
        redirect("{$uri}/list");
      }
      catch (Exception \$e) {
        setmsg(\$e->getMessage(), "error");
      }
    }
    \$this->viewData["form"] = \$Form->render();
    return \$this->view("edit");
  }
  
  public function deleteAction(\$args = []) {
    if (empty(\$args[0]))
      return \$this->notFound();
    \${$name} = \$this->getEntity("{$name}", \$args[0]);
    if (!\${$name}->id())
      return \$this->notFound();
    \$Form = \$this->getForm("Confirm", ["text" => t("Are you sure you want to delete the {$singular_lower}?")]);
    if (\$Form->isSubmitted()) {
      try {
        \$this->Model->delete{$name}(\${$name});
        setmsg(t("{$singular} deleted"), "success");
        redirect("{$uri}/list");
      }
      catch (Exception \$e) {
        setmsg(\$e->getMessage(), "error");
      }
    }
    \$this->viewData["form"] = \$Form->render();
    return \$this->view("delete");
  }
  
  public function listAction() {
    \$values = (!empty(\$_SESSION["{$snake}_list_search"]) ? \$_SESSION["{$snake}_list_search"] : []);
    \$Form = \$this->getForm("Search", \$values);
    if (\$Form->isSubmitted()) {
      \$_SESSION["{$snake}_list_search"] = \$Form->values();
      redirect("{$uri}/list");
    }
    \$Pager = newClass("Pager");
    \$Pager->ppp = 30;
    \$Pager->setNum(\$this->Model->listSearchNum(\$values));
    \$values["limit"] = [\$Pager->start(), \$Pager->ppp];
    \$this->viewData["search"] = \$Form->render();
    \$this->viewData["pager"] = \$Pager->render();
    \$this->viewData["items"] = \$this->Model->listSearch(\$values);
    return \$this->view("list");
  }
  
}
EOD;
  }
  
  public static function fileModel($args) {
    extract($args);
    return <<<EOD
<?php
class {$name}_Model extends Model {
  
  public function add{$name}(\$values) {
    return \$this->edit{$name}(\$this->getEntity("{$name}"), \$values);
  }
  
  public function edit{$name}(\${$name}, \$values) {
    foreach (\$values as \$key => \$value) {
      if (!is_array(\$value))
        \${$name}->set(\$key, \$value);
    }
    \${$name}->save();
  }
  
  public function delete{$name}(\${$name}) {
    \${$name}->delete();
  }
  
  public function listSearchQuery(\$values) {
    \$query = [
      "from" => "{$snake}",
      "cols" => ["id"],
    ];
    if (!empty(\$values["q"])) {
      \$qs = explode(" ", \$values["q"]);
      foreach (\$qs as \$i => \$q) {
        \$key = ":q".\$i;
        // \$query["where"][] = "title LIKE ".\$key;
        // \$query["vars"][\$key] = \$q;
      }
    }
    if (!empty(\$values["limit"]))
      \$query["limit"] = \$values["limit"];
    return \$query;
  }
  
  public function listSearchNum(\$values) {
    return \$this->Db->numRows(\$this->listSearchQuery(\$values));
  }
  
  public function listSearch(\$values) {
    return \$this->getEntities("{$name}", \$this->Db->getRows(\$this->listSearchQuery(\$values)));
  }
  
}
EOD;
  }
  
  public static function fileEditForm($args) {
    extract($args);
    return <<<EOD
<?php
class {$name}Edit_Form extends Form {
  
  protected function structure() {
    \${$name} = \$this->get("{$name}");
    \$form = [
      "name" => "{$snake}_edit",
      "items" => [
        "actions" => \$this->defaultActions(),
      ],
    ];
    return \$form;
  }
  
}
EOD;
  }
  
  public static function fileEntity($args) {
    extract($args);
    return <<<EOD
<?php
class {$name}_Entity extends Entity {
  
  protected function schema() {
    \$schema = parent::schema();
    \$schema["table"] = "{$snake}";
    \$schema["fields"]+= [
      
    ];
    return \$schema;
  }
  
}
EOD;
  }
  
  public static function fileViewAdd($args) {
    extract($args);
    return <<<EOD
<?php
\$this->Html->h1 = \$this->Html->title = t("Add {$singular_lower}");
\$this->Html->breadcrumbs[] = ["{$uri}/list", t("{$plural}")];
\$this->Html->breadcrumbs[] = \$this->Html->title;

print \$form;
EOD;
  }
  
  public static function fileViewEdit($args) {
    extract($args);
    return <<<EOD
<?php
\$this->Html->h1 = \$this->Html->title = t("Edit {$singular_lower}");
\$this->Html->breadcrumbs[] = ["{$uri}/list", t("{$plural}")];
\$this->Html->breadcrumbs[] = \$this->Html->title;

print \$form;
EOD;
  }
  
  public static function fileViewDelete($args) {
    extract($args);
    return <<<EOD
<?php
\$this->Html->h1 = \$this->Html->title = t("Delete {$singular_lower}");
\$this->Html->breadcrumbs[] = ["{$uri}/list", t("{$plural}")];
\$this->Html->breadcrumbs[] = \$this->Html->title;

print \$form;
EOD;
  }
  
  public static function fileViewList($args) {
    extract($args);
    return <<<EOD
<?php
\$this->Html->h1 = \$this->Html->title = t("{$plural}");
\$this->Html->breadcrumbs[] = \$this->Html->title;
?>

<a class="btn btn-primary" href="<?=url("{$uri}/add")?>"><?=t("Add {$singular_lower}")?></a>

<?=\$search?>

<table class="striped {$uri}-list">
  <thead>
    <tr>
      <th class="actions"><?=t("Actions")?></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach (\$items as \${$name}) { ?>
    <tr>
      <td class="actions">
        <a href="<?=url("{$uri}/edit/".\${$name}->id())?>"><?=t("Edit")?></a>
        <a href="<?=url("{$uri}/delete/".\${$name}->id())?>"><?=t("Delete")?></a>
      </td>
    </tr>
  <?php } ?>
  </tbody>
</table>

<?=\$pager?>
EOD;
  }
  
}