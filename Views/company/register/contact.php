<?php
use System\Web\Utils\HTMLUtils;
echo HTMLUtils::renderTextArea(__('address.street','Street address'), 'address_street',null,array('class'=>'required'));
echo HTMLUtils::renderTextBox(__('address.city','City'), 'address_city',null,array('class'=>'required'));
echo HTMLUtils::renderTextBox(__('address.postal_code','Postal Code'), 'company_zip',null,array('class'=>'required'));
echo HTMLUtils::renderTextBox(__('email','Email'), 'email',null,array('class'=>'required'));
echo HTMLUtils::renderTextBox(__('phonenumber','phone').'&nbsp;1', 'company_phone1',null,array('class'=>'required'));
echo HTMLUtils::renderTextBox(__('phonenumber','Phone').'&nbsp;2', 'company_phone2');
?>