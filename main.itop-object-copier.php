<?php

// Copyright (C) 2014 Combodo SARL
//
//   This file is part of iTop.
//
//   iTop is free software; you can redistribute it and/or modify	
//   it under the terms of the GNU Affero General Public License as published by
//   the Free Software Foundation, either version 3 of the License, or
//   (at your option) any later version.
//
//   iTop is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU Affero General Public License for more details.
//
//   You should have received a copy of the GNU Affero General Public License
//   along with iTop. If not, see <http://www.gnu.org/licenses/>



interface iObjectCopierActionProvider
{
	public function EnumVerbs();
	public function ExecAction($sVerb, $aParams, $oObjectToRead, $oObjectToWrite);
}

class iTopObjectCopier implements iPopupMenuExtension, iObjectCopierActionProvider
{
	/**
	 * Checks the structure and logs errors if issues have been encountered
	 */
	public static function IsRuleValid($iRule, $aRuleData)
	{
		$bRet = true;
		if (!isset($aRuleData['source_scope']))
		{
			IssueLog::Error('Module itop-object-copy - invalid rule #'.$iRule.' - missing "source_scope"');
			$bRet = false;
		}
		if (!isset($aRuleData['dest_class']))
		{
			IssueLog::Error('Module itop-object-copy - invalid rule #'.$iRule.' - missing "dest_class"');
			$bRet = false;
		}
		if (!isset($aRuleData['preset']))
		{
			IssueLog::Error('Module itop-object-copy - invalid rule #'.$iRule.' - missing "preset"');
			$bRet = false;
		}
		if (!isset($aRuleData['retrofit']))
		{
			IssueLog::Error('Module itop-object-copy - invalid rule #'.$iRule.' - missing "retrofit"');
			$bRet = false;
		}
		if (!isset($aRuleData['allowed_profiles']))
		{
			IssueLog::Error('Module itop-object-copy - invalid rule #'.$iRule.' - missing "allowed_profiles"');
			$bRet = false;
		}

		if (!is_array($aRuleData['preset']))
		{
			IssueLog::Error('Module itop-object-copy - invalid rule #'.$iRule.' - preset must be an array');
			$bRet = false;
		}
		if (!is_array($aRuleData['retrofit']))
		{
			IssueLog::Error('Module itop-object-copy - invalid rule #'.$iRule.' - retrofit must be an array');
			$bRet = false;
		}
		return $bRet;
	}

	/**
	 * Get the list of items to be added to a menu.
	 *
	 * This method is called by the framework for each menu.
	 * The items will be inserted in the menu in the order of the returned array.
	 * @param int $iMenuId The identifier of the type of menu, as listed by the constants MENU_xxx
	 * @param mixed $param Depends on $iMenuId, see the constants defined above
	 * @return object[] An array of ApplicationPopupMenuItem or an empty array if no action is to be added to the menu
	 */
	public static function EnumItems($iMenuId, $param)
	{
		$aRules = MetaModel::GetModuleSetting('itop-object-copier', 'rules', array());

		$aRet = array();
		if ($iMenuId == iPopupMenuExtension::MENU_OBJDETAILS_ACTIONS)
		{
			$oObject = $param;

			$oUser = UserRights::GetUserObject();
			$aUserProfiles = array();
			if (!is_null($oUser))
			{
				$oProfileSet = $oUser->Get('profile_list');
				while ($oProfile = $oProfileSet->Fetch())
				{
					$aUserProfiles[$oProfile->Get('profile')] = true;
				}
			}

			foreach($aRules as $iRule => $aRuleData)
			{
				if (self::IsRuleValid($iRule, $aRuleData))
				{
					$bAllowed = false;
					if (!isset($aRuleData['allowed_profiles']) || ($aRuleData['allowed_profiles'] == ''))
					{
						$bAllowed = true;
					}
					else
					{
						$sAllowedProfiles = $aRuleData['allowed_profiles'];
						foreach (explode(',', $sAllowedProfiles) as $sProfileRaw)
						{
							$sProfileName = trim($sProfileRaw);
							if (isset($aUserProfiles[$sProfileName]))
							{
								$bAllowed = true;
								break;
							}
						}
					}
	
					if ($bAllowed)
					{
						$oFilter = DBObjectSearch::FromOQL($aRuleData['source_scope']);
						if (MetaModel::IsParentClass($oFilter->GetClass(), get_class($oObject)))
						{
							$oFilter->AddCondition('id', $oObject->GetKey(), '=');
							$oCheckSet = new DBObjectSet($oFilter);
							if ($oCheckSet->Count() > 0)
							{
								$oAppContext = new ApplicationContext();
				       		//$sContextForURL = $oAppContext->GetForLink();
				       		$aParams = $oAppContext->GetAsHash();
				
								$aParams['operation'] = 'new';
								$aParams['rule'] = $iRule;
								$aParams['source_id'] = $oObject->GetKey();
								$aParams['source_class'] = get_class($oObject);
								$aRet[] = new URLPopupMenuItem
								(
									'object_copier_'.$iRule,
									self::FormatMessage($aRuleData, 'menu_label'),
									utils::GetAbsoluteUrlModulePage('itop-object-copier', 'copy.php', $aParams)
								);
							}
						}
					}
				}
			}
		}
		return $aRet;
	}

	/**
	 * Prepare the destination object for user configuration (not saved yet!)
	 */	 	
	public static function PrepareObject($aRuleData, $oDestObject, $oSourceObject)
	{
		self::ExecActions($aRuleData['preset'], $oSourceObject, $oDestObject);
	}

	/**
	 * Retrofit some information on the source object
	 */	 	
	public static function RetrofitOnSourceObject($aRuleData, $oSavedObject, $oSourceObject)
	{
		self::ExecActions($aRuleData['retrofit'], $oSavedObject, $oSourceObject);
	}

	/**
	 * Preset the object to create or retrofit some values...	
	 */	
	protected static function ExecActions($aActions, $oObjectToRead, $oObjectToWrite)
	{
		static $aVerbToProvider = array();
		if (count($aVerbToProvider) == 0)
		{
			foreach(get_declared_classes() as $sPHPClass)
			{
				$oRefClass = new ReflectionClass($sPHPClass);
				$oExtensionInstance = null;
				if ($oRefClass->implementsInterface('iObjectCopierActionProvider'))
				{
					$oActionProvider = new $sPHPClass;
					foreach ($oActionProvider->EnumVerbs() as $sVerb)
					{
						$aVerbToProvider[$sVerb] = $oActionProvider;
					}
				}
			}
		}

		foreach($aActions as $sAction)
		{
			try
			{
				if (preg_match('/^(\S*)\s*\((.*)\)$/', $sAction, $aMatches))
				{
					$sVerb = $aMatches[1];
					$sParams = $aMatches[2];
		
		// NOTE: escaping!!!
		
					$aParams = explode(',', $sParams);
		
					if (!array_key_exists($sVerb, $aVerbToProvider))
					{
						throw new Exception("Unknown verb '$sVerb'");
					}
					$oActionProvider = $aVerbToProvider[$sVerb];
					$oActionProvider->ExecAction($sVerb, $aParams, $oObjectToRead, $oObjectToWrite);
				}
				else
				{
					throw new Exception("Invalid syntax");
				}
			}
			catch(Exception $e)
			{
				throw new Exception('itop-object-copier - Action: '.$sAction.' - '.$e->getMessage());
			}
		}
	}

	public function EnumVerbs()
	{
		return array('clone', 'clone_scalars', 'copy', 'reset', 'set', 'append', 'add_to_list');
	}

	protected function GetAttValue($oObject, $sAttCode)
	{
		if ($sAttCode == 'id')
		{
			$ret = $oObject->GetKey();
		}
		else
		{
			$ret = $oObject->Get($sAttCode);
		}
		return $ret;
	}

	public function ExecAction($sVerb, $aParams, $oObjectToRead, $oObjectToWrite)
	{
		switch($sVerb)
		{
		case 'clone':
			foreach($aParams as $sAttCode)
			{
				if (MetaModel::IsValidAttCode(get_class($oObjectToWrite), $sAttCode))
				{
					$oObjectToWrite->Set($sAttCode, $this->GetAttValue($oObjectToRead, $sAttCode));
				}
			}
			break;

		case 'clone_scalars':
			foreach(MetaModel::ListAttributeDefs(get_class($oObjectToWrite)) as $sAttCode => $oAttDef)
			{
				if ($oAttDef->IsScalar())
				{
					$oObjectToWrite->Set($sAttCode, $this->GetAttValue($oObjectToRead, $sAttCode));
				}
			}
			break;

		case 'copy':
			$sSourceAttCode = $aParams[0];
			$sDestAttCode = $aParams[1];
			$oObjectToWrite->Set($sDestAttCode, $this->GetAttValue($oObjectToRead, $sSourceAttCode));
			break;

		case 'reset':
			$sAttCode = $aParams[0];
			$oAttDef = MetaModel::GetAttributeDef(get_class($oObjectToWrite), $sAttCode);
			$oObjectToWrite->Set($sAttCode, $oAttDef->GetDefaultValue());
			break;

		case 'set':
			$sAttCode = $aParams[0];
			$sRawValue = $aParams[1];
			$aContext = $oObjectToRead->ToArgs('this');
			$sValue = MetaModel::ApplyParams($sRawValue, $aContext);
			$oObjectToWrite->Set($sAttCode, $sValue);
			break;

		case 'append':
			$sAttCode = $aParams[0];
			$sRawAddendum = $aParams[1];
			$aContext = $oObjectToRead->ToArgs('this');
			$sAddendum = MetaModel::ApplyParams($sRawAddendum, $aContext);
			$oObjectToWrite->Set($sAttCode, $this->GetAttValue($oObjectToWrite, $sAttCode).$sAddendum);
			break;
		
		case 'add_to_list':
			$sSourceKeyAttCode = $aParams[0];
			$sTargetListAttCode = $aParams[1]; // indirect !!!
			if (isset($aParams[2]))
			{
				$sRoleAttCode = $aParams[2];
				$sRoleValue = $aParams[3];
			}

			$iObjKey = $this->GetAttValue($oObjectToRead, $sSourceKeyAttCode);
			if ($iObjKey > 0)
			{
				$oLinkSet = $oObjectToWrite->Get($sTargetListAttCode);

				$oListAttDef = MetaModel::GetAttributeDef(get_class($oObjectToWrite), $sTargetListAttCode);
				$oLnk = MetaModel::NewObject($oListAttDef->GetLinkedClass());
				$oLnk->Set($oListAttDef->GetExtKeyToRemote(), $iObjKey);
				if (isset($sRoleAttCode))
				{
					$oLnk->Set($sRoleAttCode, $sRoleValue);
				}
				$oLinkSet->AddObject($oLnk);
				$oObjectToWrite->Set($sTargetListAttCode, $oLinkSet);
			}
			break;
		
		default:
			throw new Exception("Invalid verb");
		}
	}

	/**
	 * Format the labels depending on the rule settings, and defaulting to dictionary entries
	 * @param aRuleData Rule settings
	 * @param sMsgCode The code in the rule settings and default dictionary (e.g. menu_label, defaulting to object-copier:menu_label:default)
	 * @param oSourceObject Optional: the source object	 	 	 
	 */	 	
	public static function FormatMessage($aRuleData, $sMsgCode, $oSourceObject = null)
	{
		$sLangCode = Dict::GetUserLanguage();
		$sCodeWithLang = $sMsgCode.'/'.$sLangCode;
		if (isset($aRuleData[$sCodeWithLang]) && strlen($aRuleData[$sCodeWithLang]) > 0)
		{
			if ($oSourceObject)
			{
				$sRet = sprintf($aRuleData[$sCodeWithLang], $oSourceObject->GetHyperlink());
			}
			else
			{
				$sRet = $aRuleData[$sCodeWithLang];
			}
		}
		else
		{
			if (isset($aRuleData[$sMsgCode]) && strlen($aRuleData[$sMsgCode]) > 0)
			{
				$sDictEntry = $aRuleData[$sMsgCode];
			}
			else
			{
				$sDictEntry = 'object-copier:'.$sMsgCode.':default';
			}
			if ($oSourceObject)
			{
				// The format function does not format if the string is not a dictionary entry
				// so we do it ourselves here
				$sFormat = Dict::S($sDictEntry);
				$sRet = sprintf($sFormat, $oSourceObject->GetHyperlink());
			}
			else
			{
				$sRet = Dict::S($sDictEntry);
			}
		}
		return $sRet;
	}
}
