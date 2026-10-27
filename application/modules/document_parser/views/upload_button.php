<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$btnText = !empty($button_text) ? $button_text : 'Upload Document';
$btnClass = !empty($button_class) ? $button_class : '';
?>
<!-- Document Parser - Upload File Button -->
<button type="button" 
        class="btn btn-primary shadow-sm font-weight-bold d-inline-flex align-items-center <?= htmlspecialchars($btnClass) ?>" 
        id="btnUploadExtractDoc" 
        onclick="if(typeof openDocParserUpload === 'function'){ openDocParserUpload(); } else { var s=document.getElementById('docExtractSection'); if(s){s.style.display='block'; s.scrollIntoView({behavior:'smooth'});} var f=document.getElementById('docExtractFileInput'); if(f){f.value=''; f.click();} }"
        style="border-radius: 8px; padding: 8px 18px; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); border: none; font-size: 13.5px; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35); transition: all 0.2s ease; cursor: pointer;">
  <i class="feather-upload-cloud mr-2 me-2" style="font-size: 16px;"></i>
  <span><?= htmlspecialchars($btnText) ?></span>
</button>
