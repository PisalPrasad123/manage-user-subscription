$ = jQuery;
$('document').ready(function() {

  // 
  $('#musForm textarea').bind('blur', function() {
    if ($(this).val() != '') {
      if ($(this).attr('id') == 'email_for_new_subscription') {
        $('#send_email_new_subscription').attr('checked','checked');
      } else if ($(this).attr('id') == 'email_for_update_subscription') {
        $('#send_email_update_subscription').attr('checked','checked');
      }
    }
  });
})