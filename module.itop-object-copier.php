<?php
//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'itop-object-copier/1.0.0',
	array(
		// Identification
		//
		'label' => 'Object copier',
		'category' => 'tooling',

		// Setup
		//
		'dependencies' => array(
			
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'main.itop-object-copier.php'
		),
		'webservice' => array(
			
		),
		'data.struct' => array(
			// add your 'structure' definition XML files here,
		),
		'data.sample' => array(
			// add your sample data XML files here,
		),
		
		// Documentation
		//
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
		'doc.more_information' => '', // hyperlink to more information, if any 

		// Default settings
		//
		'settings' => array(
			'rules' => array(
				array(
					'source_scope' => 'SELECT Location',
					'allowed_profiles' => 'Administrator,Configuration Manager',
					'menu_label' => 'Clone...', // Label or dictionary entry
					'form_label' => 'Cloning %1$s', // Label or dictionary entry
					'report_label' => 'Cloned from %1$s', // Label or dictionary entry
					'dest_class' => 'Location', // Class of the new object
					'preset' => array( // Series of actions to preset the object in the creation form
						'clone_scalars(*)',
						'reset(name)',
					),
					'retrofit' => array( // Series of actions to retrofit some information from the created object to the source object
					),
				),
				array(
					'source_scope' => 'SELECT UserRequest',
					'allowed_profiles' => '', // Empty => anybody
					'menu_label' => 'Clone...', // Label or dictionary entry
					'form_label' => 'Cloning %1$s', // Label or dictionary entry
					'report_label' => 'Cloned from %1$s', // Label or dictionary entry
					'dest_class' => 'UserRequest', // Class of the new object
					'preset' => array( // Series of actions to preset the object in the creation form
						'clone_scalars()',
						'add_to_list(caller_id,contacts_list,role,Caller for the parent)',
						'copy(id,parent_request_id)'
						//'clone(name,city)',
						//'reset(name)',
						//'copy(name,address)',
						//'copy(name,country)',
						//'append(address, et on y boit du whisky)',
						//'set(name,<mettez ici le nom>)',
					),
					'retrofit' => array(// Series of actions to retrofit some information from the created object to the source object
						//'copy(id, parent_request_id)'
					),
				),
				array(
					'source_scope' => 'SELECT FunctionalCI',
					'allowed_profiles' => 'Administrator,Configuration Manager',
					'dest_class' => 'Location', // Class of the new object
					'preset' => array( // Series of actions to preset the object in the creation form
						'clone_scalars(*)',
						'reset(name)',
					),
					'retrofit' => array( // Series of actions to retrofit some information from the created object to the source object
					),
				),
			)
		),
	)
);


?>
