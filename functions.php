<?php
/*
  General use functions, unrelated to plugin functionality
*/

/*
  write table row with input field
*/
function inputData($type,$label,$value = '',$attributes = array()) {
  // variables used in this function
  $output = $tagAttributes = $beforeField = $afterField = '';
  $radioOptions = array();

  // divide attributes
  foreach ($attributes as $key => $attrValue) {
    switch ($key) {
      case 'readonly':
        $tagAttributes .= ' readonly';
        break;
      case 'disabled':
        $tagAttributes .= ' disabled';
        break;
      case 'description':
        $afterField .= '<br><span class="description">'.$attrValue.'</span>';
        break;
      default:
        # code...
        break;
    }
  }

  // build the fields
  switch ($type) {
    case 'date':
      $output = '
      <tr>
        <th><label for="'.slug($label).'">'.$label.'</label></th>
        <td>
          '.$beforeField.'
          <input type="date" name="'.slug($label).'" id="'.slug($label).'"
            value="'.$value.'"
            '.$tagAttributes.'
          />
          '.$afterField.'
        </td>
      </tr>';
      break;
    case 'textarea':
      $output = '
      <tr>
        <th><label for="'.slug($label).'">'.$label.'</label></th>
        <td>
          '.$beforeField.'
          <textarea name="'.slug($label).'" id="'.slug($label).'" placeholder=""
            rows="5" cols="30"
            '.$tagAttributes.'
          >'.$value.'</textarea>
          '.$afterField.'
        </td>
      </tr>';
      break;

    case 'radio':
      // build the radio buttons
    
      foreach ($value as $inputValue) {
        $radioOptions[] = '
          <input type="radio" name="'.slug($label).'"
            value="'.slug($inputValue['label']).'" id="'.slug($label.$inputValue['label']).'"
            '.(isset($inputValue['checked']) && $inputValue['checked'] ? 'checked="checked"' : '').'
          />
          <label for="'.slug($label.$inputValue['label']).'">'.$inputValue['label'].'</label>';
      }
      $radioOptions = implode('<br>', $radioOptions);

      $output = '
        <tr>
          <th><label for="'.slug($label).'">'.$label.'</label></th>
          <td>'.$radioOptions.'</td>
        </tr>';
      break;
    
    default:
      $output = '
      <tr>
        <th><label for="'.slug($label).'">'.$label.'</label></th>
        <td>
          '.$beforeField.'
          <input type="text" name="'.slug($label).'" id="'.slug($label).'"
            value="'.$value.'"
            '.$tagAttributes.'
          />
          '.$afterField.'
        </td>
      </tr>';
      break;
  }

  return $output;
}

/*
  slug-ify strings for url or html attributes
*/
function slug($text) {
  $text = str_replace (' ', '_', $text);
  $text = preg_replace('/[^a-z0-9_]/i', '', $text);
  $text = strtolower($text);
  return $text;
}

/*
  add javascript files
*/
function addJs($params) {
  $scripts = '';
  $jss = array('date','mus');
  foreach ($jss as $js) {
    if (in_array($js, $params)) {
      $scripts .= '<script type="text/javascript" src="../wp-content/plugins/manage-user-subscription/js/'.$js.'.js"></script>'."\n";
    }
  }
  return $scripts;
}