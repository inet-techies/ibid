<?php $employee_det = $this->session->userdata('logged_in');
   $theme_path = $this->config->item('theme_locations').$this->config->item('active_template');

  if(empty($employee_det)) { redirect($this->config->item('base_url').'bid/index'); }
?>
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$btnText = !empty($button_text) ? $button_text : 'Upload Document';
$btnClass = !empty($button_class) ? $button_class : '';
?>
<!-- Document Parser - Upload File Button -->
<button type="button" 
        class="btn btn-primary btn-doc-parser-upload shadow-sm font-weight-bold d-inline-flex align-items-center <?= htmlspecialchars($btnClass) ?>" 
        id="btnUploadExtractDoc" 
        onclick="if(typeof openDocParserUpload === 'function'){ openDocParserUpload(); } else { var s=document.getElementById('docExtractSection'); if(s){s.style.display='block'; s.scrollIntoView({behavior:'smooth'});} var f=document.getElementById('docExtractFileInput'); if(f){f.value=''; f.click();} }">
  <i class="feather-upload-cloud mr-2 me-2" style="font-size: 16px;"></i>
  <span><?= htmlspecialchars($btnText) ?></span>
</button>
