<?php
//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
    'itop-object-copier/1.4.5',
    array(
        // Identification
        //
        'label' => 'Object copier',
        'category' => 'tooling',

        // Setup
        //
        'dependencies' => array(),
        'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'main.itop-object-copier.php'
		),
		'webservice' => array(),
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
		// This should be defined in XML to allow overrides (see https://wiki.openitop.org/doku.php?id=2_4_0:customization:xml_reference&s[]=xml&s[]=data&s[]=model&s[]=reference#modules_parameters)
		// But we can't because of label keys that contains also the locale ("/" and " " characters)
		'settings' => array(
			'rules' => array(
				'clone_location' => array(
					'source_scope' => 'SELECT Location',
					'allowed_profiles' => 'Administrator,Configuration Manager',
					'menu_label' => 'Clone...', // Label or dictionary entry
					'menu_label/FR FR' => 'Cloner...', // Label
					// Tooltip is needed if the action is in "shortcut_actions" 
					'menu_tooltip' => 'Clone this location...',
					'menu_tooltip/FR FR' => 'Cloner ce lieu...',
					'icon' => 'fas fa-clone',
					'form_label' => 'Cloning %1$s', // Label or dictionary entry
					'form_label/FR FR' => 'Clonage de %1$s', // Label
					'report_label' => 'Cloned from %1$s', // Label or dictionary entry
					'report_label/FR FR' => 'Cloné depuis %1$s', // Label
					'dest_class' => 'Location', // Class of the new object
					'preset' => array( // Series of actions to preset the object in the creation form
						'clone_scalars(*)',
						'reset(name)',
					),
					'retrofit' => array( // Series of actions to retrofit some information from the created object to the source object
					),
				),
				'child_userrequest' => array(
					'source_scope' => "SELECT UserRequest WHERE status NOT IN ('resolved','closed')",
					'allowed_profiles' => 'Support Agent,Administrator',
					'menu_label' => 'Create a child request...', // Label or dictionary entry
					'menu_label/FR FR' => 'Créer une demande fille...', // Label
					'menu_tooltip' => 'Create a child request of this request...',
					'menu_tooltip/FR FR' => 'Créer une demande fille de cette demande...',
					'icon' => 'fas fa-comment-alt',
					'form_label' => 'Create a child request from %1$s', // Label or dictionary entry
					'form_label/FR FR' => 'Créer une demande fille depuis %1$s', // Label
					'report_label' => 'Created from %1$s', // Label or dictionary entry
					'report_label/FR FR' => 'Créée depuis %1$s', // Label
					'dest_class' => 'UserRequest', // Class of the new object
					'preset' => array ( // Series of actions to preset the object in the creation form
						'clone(caller_id,org_id,contacts_list,functionalcis_list)',
						'copy(id,parent_request_id)',
					),
					'retrofit' => array( // Series of actions to retrofit some information from the created object to the source object
					),
				),
				'userrequest_from_person' => array(
					'source_scope' => 'SELECT Person',
					'allowed_profiles' => 'Support Agent,Administrator',
					'menu_label' => 'Create a user request...',
					'menu_label/FR FR' => 'Créer une demande utilisateur...',
					'menu_tooltip' => 'Create a user request for this person...',
					'menu_tooltip/FR FR' => 'Créer une demande utilisateur pour cette personne...',
					'icon' => 'fas fa-comment-alt',
					'form_label' => 'Create a user request from %1$s',
					'form_label/FR FR' => 'Créer une demande utilisateur pour %1$s',
					'report_label' => 'Created from %1$s',
					'report_label/FR FR' => 'Créée depuis %1$s',
					'dest_class' => 'UserRequest',
					'preset' => array(
						'copy(org_id,org_id)',
						'copy(id,caller_id)',
					),
					'retrofit' => array( // Series of actions to retrofit some information from the created object to the source object
					),
				),
				'userrequest_from_ci' => array(
					'source_scope' => 'SELECT FunctionalCI',
					'allowed_profiles' => 'Support Agent,Administrator',
					'menu_label' => 'Create a user request...',
					'menu_label/FR FR' => 'Créer une demande utilisateur...',
					'menu_tooltip' => 'Create a user request for this CI...',
					'menu_tooltip/FR FR' => 'Créer une demande utilisateur pour cet objet...',
					'icon' => 'fas fa-comment-alt',
					'form_label' => 'Create a user request from  %1$s',
					'form_label/FR FR' => 'Créer une demande utilisateur pour %1$s',
					'report_label' => 'Created from %1$s',
					'report_label/FR FR' => 'Créée depuis %1$s',
					'dest_class' => 'UserRequest',
					'preset' => array(
						'copy(org_id,org_id)',
						'add_to_list(id,functionalcis_list,impact,Impacted CI)',
					),
					'retrofit' => array( // Series of actions to retrofit some information from the created object to the source object
					),
				),
				'clone_ci' => array(
					'menu_tooltip' => 'Clone this CI...',
					'menu_tooltip/FR FR' => 'Cloner cet élément...',
					'icon' => 'fas fa-clone',
					'source_scope' => 'SELECT FunctionalCI',
					'allowed_profiles' => 'Administrator,Configuration Manager',
					'dest_class' => '', // Same class as the source object
					'preset' => array( // Series of actions to preset the object in the creation form
						'clone_scalars(*)',
						'reset(name)',
					),
					'retrofit' => array( // Series of actions to retrofit some information from the created object to the source object
					),
				),
				'userrequest_from_log' => array(
					'source_scope' => 'SELECT UserRequest WHERE status IN (\'closed\')',
					'allowed_profiles' => 'Support Agent,Administrator',
					'menu_label' => 'Create ticket with last log...',
					'menu_label/FR FR' => 'Créer une demande depuis le journal...',
					'menu_tooltip' => 'Create a ticket based on last log entry...',
					'menu_tooltip/FR FR' => 'Créer une demande basée sur la dernière entrée du journal...',
					'icon' => 'far fa-comment-alt',
					'form_label' => 'Create new request from %1$s',
					'form_label/FR FR' => 'Nouvelle demande depuis %1$s',
					'report_label' => 'Created from %1$s',
					'report_label/FR FR' => 'Créée depuis %1$s',
					'dest_class' => 'UserRequest',
					'preset' =>
						array(
							0 => 'clone(caller_id,org_id,contacts_list,functionalcis_list)',
							1 => 'copy(id,parent_request_id)',
							2 => 'copy_head(public_log,description)',
						),
					'retrofit' =>
						array(),
				),
				'MailInboxStandardToMailInboxOAuth' => array(
					'source_scope' => 'SELECT MailInboxStandard WHERE finalclass = "MailInboxStandard"',
					'allowed_profiles' => 'Administrator',
					'menu_label' => 'Create OAuth 2.0 Mail Inbox...',
					'menu_label/FR FR' => 'Créer une Boite mail OAuth 2.0...',
					'form_label' => 'Create new OAuth 2.0 Mail Inbox from %1$s',
					'form_label/FR FR' => 'Nouvelle Boite mail OAuth 2.0 depuis %1$s',
					'report_label' => 'Created from %1$s',
					'report_label/FR FR' => 'Créée depuis %1$s',
					'dest_class' => 'MailInboxOAuth',
					'preset' => array(
						0 => 'clone(server, mailbox,login,port,behavior, target_class, ticket_default_values, ticket_default_title, title_pattern,unknown_caller_behavior,unknown_caller_rejection_reply,caller_default_values,error_behavior, notify_errors_to, notify_errors_from,trace,email_storage,target_folder,import_additional_contacts,stimuli )',
						1 => 'set(active,no)',
					),
					'retrofit' => array(),
				),
			)
		),
	)
);
