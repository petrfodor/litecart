<?php
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  document::$snippets['head_tags']['noindex'] = '<meta name="robots" content="noindex" />';
  
  document::$layout = 'printable';
  
  if (empty($_GET['order_id']) || empty($_GET['checksum'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
  }
  
  document::$snippets['title'][] = language::translate('title_order', 'Order') .' #'. $_GET['order_id'];
  //document::$snippets['keywords'] = '';
  //document::$snippets['description'] = '';
  
  $order = new ctrl_order('load', $_GET['order_id']);
  
  if ($_GET['checksum'] != functions::general_order_public_checksum($order->data['id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
  }
  
  echo $order->draw_printable_copy();
  
  require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
?>