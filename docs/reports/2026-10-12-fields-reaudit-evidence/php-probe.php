<?php
/** Native Joomla validation probe, using the existing offline-only bootstrap. */
$_SERVER['HTTP_HOST']='offline-fixture.local';
$_SERVER['SCRIPT_NAME']='/index.php';
$_SERVER['REQUEST_URI']='/index.php';
require dirname(__DIR__, 2) . '/tests/deliverypoints.php';
$form = new \Joomla\CMS\Form\Form('audit-validation', ['control'=>'audit']);
if (method_exists($form, 'setDatabase')) $form->setDatabase($fixtureDatabase);
$form->load('<form addfieldprefix="Webtolk\Cdekapi\Fields"><fieldset name="audit"><field name="point" type="Deliverypoints" city="Саратов" validate="options" required="true" /></fieldset></form>');
$form->bind(['point'=>'SAVED']);
$point = $form->getField('point');
\Webtolk\Cdekapi\Cdek::$cities=[['code'=>428]];
\Webtolk\Cdekapi\Cdek::$points=[['code'=>'NEW','type'=>'PVZ','name'=>'Existing provider point']];
$actual=(new \Webtolk\Cdekapi\Service\DeliverypointsService())->getOptions(new \Webtolk\Cdekapi\Cdek(),'Саратов','');
$valid = $point->validate('NEW');
$stored = $point->validate('SAVED');
$tariffForm = new \Joomla\CMS\Form\Form('audit-tariff-validation', ['control'=>'audit']);
if (method_exists($tariffForm, 'setDatabase')) $tariffForm->setDatabase($fixtureDatabase);
$tariffForm->load('<form addfieldprefix="Webtolk\Cdekapi\Fields"><fieldset name="audit"><field name="tariff" type="Tarifflist" validate="options" required="true" /></fieldset></form>');
$tariffValidation=$tariffForm->getField('tariff')->validate('136');
$result = [
 'provider_options'=>array_column($actual,'value'),
 'field_server_options'=>array_map(static fn($option)=>$option->value,$point->options),
 'selected_provider_point_passes_options_rule'=>$valid === true,
 'stored_unknown_point_passes_options_rule'=>$stored === true,
 'validation_error_type'=>is_object($valid)?get_class($valid):null,
 'validation_error_message'=>is_object($valid)?$valid->getMessage():null,
 'tariff_136_passes_options_rule'=>$tariffValidation === true,
 'tariff_issue_preexisting'=>true,
 'network'=>'none',
];
echo json_encode($result,JSON_UNESCAPED_SLASHES),PHP_EOL;
file_put_contents(__DIR__.'/php-result.json',json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
