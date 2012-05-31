<?php
use System\MVC\MVCHelper;

use System\Web\Utils\HTMLUtils;
echo HTMLUtils::renderSelect(__('country'), 'country', MVCHelper::lookup('country',\CGAF::APP_ID),'id',null);
echo HTMLUtils::renderTextBox(__('company.company_name','Company/Organization'), 'company_name',null,array(
    'class'=>'required'
));
echo HTMLUtils::renderTextBox(__('website'), 'website');
echo HTMLUtils::renderCheckbox(__('nowebsite','I don\'t have a website.'), 'nowebsite');
echo HTMLUtils::renderTextArea(__('description','Description'), 'description');
echo HTMLUtils::renderTextArea(__('category','Category'), 'category');
?>