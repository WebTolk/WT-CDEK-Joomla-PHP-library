<?php
$_SERVER['HTTP_HOST']='joomla.local';
$argv[1]='E:/OSPanel/home/joomla.local/public';
$fixture=file_get_contents('.webtolk/tests/deliverypoints.php');
$cut=strpos($fixture,'    $map=htmlspecialchars(');
if ($cut===false) throw new RuntimeException('No fixture boundary');
$bootstrap=substr($fixture,5,$cut-5);
$bootstrap=str_replace("\$repo.'/lib_webtolk_wtcdek/src'","\$core.'/libraries/Webtolk/Cdekapi/src'",$bootstrap);
$bootstrap=str_replace('public function location()','public function calculator() { return new class { public function getAllTariffs() { return [["tariff_name"=>"Offline tariff","delivery_modes"=>[["tariff_code"=>136,"delivery_mode"=>"1","delivery_mode_name"=>"Offline mode"]]]]; } }; } public function location()',$bootstrap);
$probe=<<<'PROBE'
    class AuditDocument extends FixtureDocument {
        public function addScriptOptions($key,$value,$merge=true) { $this->options[$key]=$merge && is_array($value) ? array_replace_recursive($this->options[$key] ?? [],$value) : $value; return $this; }
        public function usePreset($name) { $this->assets[]='preset:'.$name; return $this; }
        public function getScriptOptions($name=null) { return $name===null ? $this->options : ($this->options[$name] ?? []); }
    }
    $app->fixtureDocument=new AuditDocument();
    JLoader::registerNamespace('Webtolk\\Jshopping\\SmWtcdek\\Fields',$core.'/components/com_jshopping/src/Fields');
    $results=[];
    foreach (['params','sm_params'] as $control) {
        $form=new \Joomla\CMS\Form\Form($control,['control'=>$control]);
        $form->setDatabase($fixtureDatabase);
        if (!$form->loadFile($core.'/components/com_jshopping/shippings/sm_wtcdek/params.xml')) throw new \RuntimeException('XML load failed');
        $form->bind(['city_from_zip'=>'410000','city_from'=>'Saratov','shipment_point'=>'SAVED','tariff_code'=>'136']);
        $classes=[];
        foreach (['city_from_zip','city_from','shipment_point','tariff_code','tariff_info','cdek_status_create'] as $name) $classes[$name]=get_class($form->getField($name));
        foreach (['shipment_point'=>'DeliverypointsField','tariff_code'=>'TarifflistField','tariff_info'=>'TariffinfoField'] as $name=>$class) {
            if ($classes[$name]!=='Webtolk\\Cdekapi\\Fields\\'.$class) throw new \RuntimeException('Wrong class: '.$name);
        }
        $pointHtml=$form->getField('shipment_point')->input;
        preg_match('/data-watchfield="([^"]*)" data-watchfield-options="([^"]*)"/',$pointHtml,$match);
        if (count($match)!==3) throw new \RuntimeException('No watchfield attributes');
        $pointSources=json_decode(html_entity_decode($match[1],ENT_QUOTES),true);
        $pointOptions=json_decode(html_entity_decode($match[2],ENT_QUOTES),true);
        if ($pointSources!==['city'=>$control.'[city_from]','postal_code'=>$control.'[city_from_zip]']) throw new \RuntimeException('Wrong source names');
        if ($pointOptions['parameters']['type']!=='PVZ' || $pointOptions['parameters']['is_reception']!=='true') throw new \RuntimeException('Wrong filters');
        if (!str_contains($pointHtml,'<joomla-field-fancy-select') || !str_contains($pointHtml,'SAVED')) throw new \RuntimeException('No FancySelect/saved value');
        $tariffHtml=$form->getField('tariff_code')->input;
        $infoHtml=$form->getField('tariff_info')->input;
        preg_match('/data-watchfield="([^"]*)"/',$infoHtml,$infoMatch);
        $infoSources=json_decode(html_entity_decode($infoMatch[1],ENT_QUOTES),true);
        if ($infoSources!==['value'=>$control.'[tariff_code]']) throw new \RuntimeException('Wrong tariff source');
        if (!str_contains($tariffHtml,'136') || !isset($app->fixtureDocument->options['wtcdek.tariffs'][136])) throw new \RuntimeException('No tariff catalog');
        $results[]=['control'=>$control,'classes'=>$classes,'point_sources'=>$pointSources,'point_options'=>$pointOptions,'fancyselect'=>true,'tariff_sources'=>$infoSources];
    }
    echo json_encode(['result'=>'passed','network'=>'none','database'=>'forbidden','installed_library'=>true,'forms'=>$results,'assets'=>$app->fixtureDocument->assets],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),"\n";
PROBE;
eval($bootstrap.$probe."\n}");
