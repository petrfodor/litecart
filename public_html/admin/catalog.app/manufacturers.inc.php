<?php

  if (!empty($_POST['enable']) || !empty($_POST['disable'])) {
  
    if (!empty($_POST['manufacturers'])) {
      foreach ($_POST['manufacturers'] as $key => $value) $_POST['manufacturers'][$key] = database::input($value);
      database::query(
        "update ". DB_TABLE_MANUFACTURERS ."
        set status = '". ((!empty($_POST['enable'])) ? 1 : 0) ."'
        where id in ('". implode("', '", $_POST['manufacturers']) ."');"
      );
    }
    
    header('Location: '. document::link());
    exit;
  }
  
?>

<div style="float: right;"><?php echo functions::form_draw_link_button(document::link('', array('app' => $_GET['app'], 'doc' => 'edit_manufacturer')), language::translate('title_add_new_manufacturer', 'Add New Manufacturer'), '', 'add'); ?></div>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h1>

<?php echo functions::form_draw_form_begin('manufacturers_form', 'post'); ?>
<table class="dataTable" width="100%">
  <tr class="header">
    <th><?php echo functions::form_draw_checkbox('checkbox_toggle', '', ''); ?></th>
    <th width="100%" align="left"><?php echo language::translate('title_name', 'Name'); ?></th>
    <th align="center"><?php echo language::translate('title_products', 'Products'); ?></th>
    <th>&nbsp;</th>
  </tr>
<?php
    $manufacturers_query = database::query(
      "select * from ". DB_TABLE_MANUFACTURERS ."
      order by name asc;"
    );
    
    if (database::num_rows($manufacturers_query) > 0) {
      while ($manufacturer = database::fetch($manufacturers_query)) {
        if (!isset($rowclass) || $rowclass == 'even') {
          $rowclass = 'odd';
        } else {
          $rowclass = 'even';
        }
        
        $num_active = database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where status and manufacturer_id = ". (int)$manufacturer['id'] .";"));
        $num_products = database::num_rows(database::query("select id from ". DB_TABLE_PRODUCTS ." where manufacturer_id = ". (int)$manufacturer['id'] .";"));
?>
  <tr class="<?php echo $rowclass . ($manufacturer['status'] ? false : ' semi-transparent'); ?>">
    <td nowrap="nowrap"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/'. (!empty($manufacturer['status']) ? 'on' : 'off') .'.png'; ?>" width="16" height="16" align="absbottom" /> <?php echo functions::form_draw_checkbox('manufacturers['. $manufacturer['id'] .']', $manufacturer['id']); ?></td>
    <td nowrap="nowrap"><img src="<?php echo (($manufacturer['image']) ? functions::image_resample(FS_DIR_HTTP_ROOT . WS_DIR_IMAGES . $manufacturer['image'], FS_DIR_HTTP_ROOT . WS_DIR_CACHE, 16, 16, 'FIT_USE_WHITESPACING') : WS_DIR_IMAGES .'no_image.png'); ?>" width="16" height="16" align="absbottom" /> <a href="<?php echo document::href_link('', array('doc' => 'edit_manufacturer', 'manufacturer_id' => $manufacturer['id']), array('app')); ?>"><?php echo $manufacturer['name']; ?></a></td>
    <td nowrap="nowrap" align="right"><?php echo (int)$num_active .' ('. (int)$num_products .')'; ?></td>
    <td nowrap="nowrap"><a href="<?php echo document::href_link('', array('app' => $_GET['app'], 'doc' => 'edit_manufacturer', 'manufacturer_id' => $manufacturer['id'])); ?>"><img src="<?php echo WS_DIR_IMAGES .'icons/16x16/edit.png'; ?>" width="16" height="16" align="absbottom" /></a></td>
  </tr>
<?php
      }
    }
?>
  <tr class="footer">
    <td colspan="4" align="left"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?>: <?php echo database::num_rows($manufacturers_query); ?></td>
  </tr>
</table>

<script>
  $(".dataTable input[name='checkbox_toggle']").click(function() {
    $(this).closest("form").find(":checkbox").each(function() {
      $(this).attr('checked', !$(this).attr('checked'));
    });
    $(".dataTable input[name='checkbox_toggle']").attr("checked", true);
  });

  $('.dataTable tr').click(function(event) {
    if ($(event.target).is('input:checkbox')) return;
    if ($(event.target).is('a, a *')) return;
    if ($(event.target).is('th')) return;
    $(this).find('input:checkbox').trigger('click');
  });
</script>

<p><?php echo functions::form_draw_button('enable', language::translate('title_enable', 'Enable'), 'submit', '', 'on'); ?> <?php echo functions::form_draw_button('disable', language::translate('title_disable', 'Disable'), 'submit', '', 'off'); ?></p>

<?php echo functions::form_draw_form_end(); ?>