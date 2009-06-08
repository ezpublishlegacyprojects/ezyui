<?php

$eZTemplateOperatorArray = array();

$eZTemplateOperatorArray[] = array( 'script' => 'extension/ezyui/autoloads/ezyuipackertemplatefunctions.php',
                                    'class' => 'eZYuiPackerTemplateFunctions',
                                    'operator_names' => array( 'ezscript',
                                                               'ezscriptfiles',
                                                               'ezcss',
                                                               'ezcssfiles' ) );


$eZTemplateOperatorArray[] = array( 'script' => 'extension/ezyui/autoloads/ezyuiutils.php',
                                    'class' => 'eZYuiUtils',
                                    'operator_names' => array( 'weeknumber',
                                                               'fetch_main_node',
                                                               'json_encode',
                                                               'xml_encode',
                                                               'node_encode'
) );
?>
