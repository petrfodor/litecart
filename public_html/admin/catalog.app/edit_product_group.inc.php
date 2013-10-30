<?php

  $product_group = new ctrl_product_group();
  
  if (!empty($_GET['product_group_id'])) {
  
    $product_group->load($_GET['product_group_id']);
    
    if (empty($product_group)) die('Invalid product group id');
    
    if (empty($_POST)) {
      foreach ($product_group->data as $key => $value) {
        $_POST[$key] = $value;
      }
    }
  }
  
  if (!empty($_POST['save'])) {
    
    if (empty($_POST['values'])) $_POST['values'] = array();
    
    if (empty($errors)) {
      $fields = array(
        'name',
        'values',
      );
      
      foreach ($fields as $field) {
        if (isset($_POST[$field])) $product_group->data[$field] = $_POST[$field];
      }
      
      $product_group->save();
 
      header('Location: '. document::link('', array('doc' => 'product_groups'), array('app')));
      exit;
    }
  }
  
  if (!empty($_POST['delete'])) {
    
    if (empty($errors)) {
      $product_group->delete();
 
      header('Location: '. document::link('', array('doc' => 'product_groups'), array('app')));
      exit;
    }
  }
  
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo !empty($product_group->data['id']) ? language::translate('title_edit_product_group', 'Edit Product Group') : language::translate('title_new_product_group', 'Create New Product Group'); ?></h1>
<?php echo functions::form_draw_form_begin('form_product_group', 'post'); ?>
<p></p>
<?php
  $use_br = false;
  foreach (array_keys(language::$languages) as $language_code) {
    if ($use_br) echo '<br />';
    echo functions::form_draw_regional_input_field($language_code, 'name['. $language_code .']', true, '');
    $use_br = true;
  }
?>

<div id="product-values">
  <h2><?php echo language::translate('title_values', 'Values'); ?></h2>
  <table width="100%" class="dataTable">
    <tr class="header">
      <th align="left" style="vertical-align: text-top;" nowrap="nowrap"><?php echo language::translate('title_id', 'ID'); ?></th>
      <th align="left" style="vertical-align: text-top; width: 100%;" nowrap="nowrap"><?php echo language::translate('title_name', 'Name'); ?></th>
      <th align="center" style="vertical-align: text-top;" nowrap="nowrap"><?php echo empty($product_group->data['id']) ? '' : language::translate('title_products', 'Products'); ?></th>
      <th align="center" style="vertical-align: text-top;" nowrap="nowrap">&nbsp;</th>
    </tr>
<?php
    if (!empty($_POST['values'])) foreach ($_POST['values'] as $key => $group_value) {
      
      $products_query = database::query(
        "select id from ". DB_TABLE_PRODUCTS ."
        where product_groups like '%". (int)$product_group->data['id'] ."-". (int)$group_value['id'] ."%';"
      );
      $num_products = database::num_rows($products_query);
?>
    <tr>
      <td align="left"><?php echo $group_value['id']; ?><?php echo functions::form_draw_hidden_field('values['. $key .'][id]', $group_value['id']); ?></td>
      <td align="left">
<?php
      $use_br = false;
      foreach (array_keys(language::$languages) as $language_code) {
        if ($use_br) echo '<br />';
        echo functions::form_draw_regional_input_field($language_code, 'values['. $key .'][name]['. $language_code .']', true, '');
        $use_br = true;
      }
?>
      </td>
      <td align="center"><?php echo $num_products; ?></td>
      <td align="right"><?php echo empty($num_products) ? '<a href="#" id="remove-group-value"><img src="'. WS_DIR_IMAGES . 'icons/16x16/remove.png' .'" width="16" height="16" /></a>' : false; ?></td>
    </tr>
  <?php
    }
  ?>
    <tr>
      <td colspan="4"><a id="add-group-value" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/add.png" width="16" height="16" /> <?php echo language::translate('title_add_group', 'Add Group Value'); ?></a></td>
    </tr>  
  </table>
<script>
  var new_value_index = 1;
  $("body").on("click", "#add-group-value", function(event) {
    event.preventDefault();
    while ($("input[name^='values[new_"+ new_value_index +"][id]']").length) new_value_index++;
<?php
        $name_fields = '';
        $use_br = false;
        foreach (array_keys(language::$languages) as $language_code) {
          if ($use_br) $name_fields .=  '<br />';
          $name_fields .= functions::form_draw_regional_input_field($language_code, 'values[new_value_index][name]['. $language_code .']', '', '');
          $use_br = true;
        }
?>
    var output = '<tr>'
               + '  <td align="left" nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', functions::form_draw_hidden_field('values[new_value_index][id]', '')); ?></td>'
               + '  <td align="left" nowrap="nowrap"><?php echo str_replace(PHP_EOL, '', $name_fields); ?></td>'
               + '  <td align="left" nowrap="nowrap">&nbsp;</td>'
               + '  <td align="right" nowrap="nowrap"><a id="remove-group-value" href="#"><img src="<?php echo WS_DIR_IMAGES; ?>icons/16x16/remove.png" width="16" height="16" title="<?php echo language::translate('title_remove', 'Remove'); ?>" /></a></td>'
               + '</tr>';
    output = output.replace(/new_value_index/g, 'new_' + new_value_index);
    $(this).closest('tr').before(output);
  });
  
  $("body").on("click", "#remove-group-value", function(event) {
    event.preventDefault();
    $(this).closest('tr').remove();
  });
</script>
</div>

<p><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (!empty($product_group->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></p>
<?php echo functions::form_draw_form_end(); ?>