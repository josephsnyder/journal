<?php

/*=========================================================================
 *
 *  Copyright OSHERA Consortium
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *         http://www.apache.org/licenses/LICENSE-2.0.txt
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *=========================================================================*/

/**
 * Resource DAO
 * Extend the default Item methods
 */
Zend_Loader::loadClass("ItemDao", BASE_PATH.'/core/models/dao');

class Journal_ResourceDao extends ItemDao
  {
  public $_model = 'Item';

  public $_associatedResources = false;
  public $_metadata = false;
  public $_revision = false;

  /** Constructor */
  public function __construct()
    {
    parent::__construct();
    $this->setName('');
    $this->setDescription('');
    $this->setType(RESOURCE_TYPE_NOT_DEFINED);
    }

  /**
   * The goal of this method is to enable the resource.
   * The user will then be able to find it using the search and filter (based on solr)
   */
  function enable()
    {
    $this->setMetaDataByQualifier("enable", "true");
    $folder = end($this->getFolders());
    $community = MidasLoader::loadModel("Folder")->getCommunity(MidasLoader::loadModel("Folder")->getRoot($folder));
    $this->setMetaDataByQualifier("issue", $folder->getKey());
    $this->setMetaDataByQualifier("community", $community->getKey());
    }

   /** Get Categories
   *
   * @return array of ids
   */
  function getCategories()
    {
    $metadata = $this->getMetaDataByQualifier("categories");
    if(!$metadata) return array();
    return explode(" ", $metadata->getValue());
    }

  /* Set Catgproes
   * @param array $categories Array of ids
   */
  function setCategories($categories)
    {
    if(is_array($categories))$this->setMetaDataByQualifier("categories", join(' ', $categories));
    }

   /** Get Authors
   *
   * @return array of names
   */
  function getAuthors()
    {
    $metadata = $this->getMetaDataByQualifier("authors");
    if(!$metadata) return array();
    $authors = explode(" ;;; ", $metadata->getValue());
    foreach($authors as &$author) $author = explode(" --- ", $author);
    return $authors;
    }
   /** Get Authors
   *
   * @return array of names
   */
  function getAuthorsFullNames()
    {
    $metadata = $this->getMetaDataByQualifier("authors");
    if(!$metadata) return array();
    $authors = explode(" ;;; ", $metadata->getValue());
    foreach($authors as &$author) $author = str_replace(" --- ", ' ' , $author);
    return $authors;
    }

  /* Set Authors
   * @param array $categories Array of ids
   */
  function setAuthors($authors)
    {
    $authorsArray = array();
    foreach($authors[0] as $key => $author)
      {
      if(!empty($authors[0][$key]) && !empty($authors[1][$key]))$authorsArray[] = $authors[0][$key]." --- ".$authors[1][$key];
      }
    $this->setMetaDataByQualifier("authors", join(" ;;; ", $authorsArray));
    }

  /** Get Tags
   *
   * @return array of string
   */
  function getTags()
    {
    $metadata = $this->getMetaDataByQualifier("tags");
    if(!$metadata) return array();
    return explode(" --- ", $metadata->getValue());
    }

   /* Set Tags
   * @param array $tags Array of values
   */
  function setTags($tags)
    {
    if(isset($tags))$this->setMetaDataByQualifier("tags", join(' --- ', array_filter($tags)));
    }

  /** Get Institution
   *
   * @return
   */
  function getInstitution()
    {
    $metadata = $this->getMetaDataByQualifier("insitution");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set Institution
   * @param
   */
  function setInstitution($institution)
    {
    $this->setMetaDataByQualifier("insitution", $institution);
    }

  /** Get Github
   *
   * @return
   */
  function getGithub()
    {
    $metadata = $this->getMetaDataByQualifier("github");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set Institution
   * @param
   */
  function setGithub($github)
    {
    $this->setMetaDataByQualifier("github", $github);
    }

  /** Get Handle
   *
   * @return
   */
  function getHandle()
    {
    $metadata = $this->getMetaDataByQualifier("handle");
    $metadataValue = "";
    if($metadata)
      {
      $metadataValue = $metadata->getValue();
      }
    if(empty($metadataValue))
      {
      // If we don't find it in the current revision, we try the first one
      $firstRevision = $this->getModel()->getRevision($this, 1);
      $metadataArray = MidasLoader::loadModel('ItemRevision')->getMetadata($firstRevision);
      $metadata = $this->_getMetaDataByQualifier($metadataArray, "handle");
      if(!$metadata)return '';
      }
    return $metadata->getValue();
    }

   /* Set Handle
   * @param
   */
  function setHandle($handle)
    {
    $this->setMetaDataByQualifier("handle", $handle);
    }

  /** Get Certification level
   *
   * @return
   */
  function getCertificationLevel()
    {
    $metadata = $this->getMetaDataByQualifier("certification_level");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

  /** Get Certification level searching through every revision
   *
   * @return
   */
  function getAllCertificationLevel($level)
    {
    list($metadata,$foundRevisionID,$foundRevisionKey) = $this->getAllMetaDataByQualifier("certification_level",$level);
    if(!$metadata) return '';
    return array($metadata->getValue(),$foundRevisionID,$foundRevisionKey);
    }

   /* Set Certification level
   * @param
   */
  function setCertificationLevel($level)
    {
    $this->setMetaDataByQualifier("certification_level", $level);
    }

  /** Get Revision Notes
   *
   * @return
   */
  function getRevisionNotes()
    {
    $metadata = $this->getMetaDataByQualifier("revision_notes");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set Revision Notes
   * @param
   */
  function setRevisionNotes($notes)
    {
    if ($notes == null) $notes = '';
    $this->setMetaDataByQualifier("revision_notes", $notes);
    }

   /** Get Submission type
   *
   * @return
   */
  function getSubmissionType()
    {
    $metadata = $this->getMetaDataByQualifier("submission_type");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set Submission type
   * @param
   */
  function setSubmissionType($type)
    {
    $this->setMetaDataByQualifier("submission_type", $type);
    }

   /* Set Has reviews
   * @param
   */
  function setHasReviews()
    {
    $this->setMetaDataByQualifier("has_reviews", "true");
    }

  /** Get Has code
   *
   * @return
   */
  function getHasCode()
    {
    $metadata = $this->getMetaDataByQualifier("has_code");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set Has code
   * @param
   */
  function setHasCode()
    {
    $this->setMetaDataByQualifier("has_code", "true");
    }

  /** Get has test code
   *
   * @return
   */
  function getTestHasCode()
    {
    $metadata = $this->getMetaDataByQualifier("has_test_code");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set has test code
   * @param
   */
  function setTestHasCode()
    {
    $this->setMetaDataByQualifier("has_test_code", "true");
    }

  /** Get Approval Status
   *  1 = waiting for approval
   *  0 = already approved or not complete
   * @return
   */
  function getApprovalStatus()
    {
    $metadata = $this->getMetaDataByQualifier("approval_status");
    if(!$metadata) return 0;
    return $metadata->getValue();
    }

   /* Set Approval Status
   * @param
   */
  function setApprovalStatus($status)
    {
    $this->setMetaDataByQualifier("approval_status", $status);
    }


  /** Get source code license
   *
   * @return
   */
  function getSourceLicense()
    {
    $metadata = $this->getMetaDataByQualifier("source_license");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set source code license
   * @param
   */
  function setSourceLicense($license)
    {
    $this->setMetaDataByQualifier("source_license", $license);
    }

  /** Get Agreed to Attribution Policy
   *  1 = agreed
   *  0 = not agreed (or not applicable)
   * @return
   */
  function getAgreedAttributionPolicy()
    {
    $metadata = $this->getMetaDataByQualifier("attribution_policy");
    if(!$metadata) return 0;
    return $metadata->getValue();
    }

   /* Set Agreed to Attribution Policy
   * @param
   */
  function setAgreedAttributionPolicy($status)
    {
    $this->setMetaDataByQualifier("attribution_policy", $status);
    }


  /** Generate new handle */
  function initHandle()
    {
    if($this->getHandle() != "")
      {
      return;
      }
    $baseHandle = MidasLoader::loadModel("Setting")->getValueByName('baseHandle', "journal");
    $metadataDao = MidasLoader::loadModel('Metadata')->getMetadata(MIDAS_METADATA_TEXT, "journal", "handle");
    if(!$metadataDao)  $metadataDao = MidasLoader::loadModel('Metadata')->addMetadata(MIDAS_METADATA_TEXT, "journal", "handle", "");
    $db = Zend_Registry::get('dbAdapter');
    $sql = $db->select()
            ->from('metadatavalue')
            ->where('metadata_id  = '.$metadataDao->getKey())
            ->order('metadatavalue_id  DESC')->limit(1);

    $row = $db->fetchRow($sql);
    $value = 1000;
    if(isset($row['value']))
      {
      $value = end(explode("/", $row['value']));
      $value = $value + 1;
      }

    // Make sure the value doesn't exist
    $exist = $this->isHandleExist($baseHandle."/".$value);
    while($exist == true)
      {
      $value++;
      $exist = $this->isHandleExist($baseHandle."/".$value);
      }

    $this->setHandle($baseHandle."/".$value);
    }

  function isHandleExist($handle)
    {
    $metadataDao = MidasLoader::loadModel('Metadata')->getMetadata(MIDAS_METADATA_TEXT, "journal", "handle");
    if(!$metadataDao)  $metadataDao = MidasLoader::loadModel('Metadata')->addMetadata(MIDAS_METADATA_TEXT, "journal", "handle", "");
    $db = Zend_Registry::get('dbAdapter');
    $sql = $db->select()
            ->from('metadatavalue')
            ->where('value  = ?', $handle)
            ->where('metadata_id  = '.$metadataDao->getKey())
            ->order('metadatavalue_id  DESC')->limit(1);

    $row = $db->fetchRow($sql);
    return isset($row['value']);
    }

  /** Get Dislcaimer Id
   *
   * @return
   */
  function getDisclaimer()
    {
    $metadata = $this->getMetaDataByQualifier("disclaimer");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set Dislcaimer Id
   * @param
   */
  function setDisclaimer($disclaimer)
    {
    $this->setMetaDataByQualifier("disclaimer", $disclaimer);
    }

  /** Get Copyright
   *
   * @return
   */
  function getCopyright()
    {
    $metadata = $this->getMetaDataByQualifier("copyright");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set Dislcaimer Id
   * @param
   */
  function setCopyright($copyright)
    {
    $this->setMetaDataByQualifier("copyright", $copyright);
    }

  /** Get Related
   *
   * @return
   */
  function getRelated()
    {
    $metadata = $this->getMetaDataByQualifier("related_work");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set Related work
   * @param
   */
  function setRelated($value)
    {
    $this->setMetaDataByQualifier("related_work", $value);
    }

  /** Get Grant
   *
   * @return
   */
  function getGrant()
    {
    $metadata = $this->getMetaDataByQualifier("grant");
    if(!$metadata) return '';
    return $metadata->getValue();
    }

   /* Set Grant
   * @param
   */
  function setGrant($value)
    {
    $this->setMetaDataByQualifier("grant", $value);
    }

  /** Get Logo
   *
   * @return
   */
  function getLogo()
    {
    $metadata = $this->getMetaDataByQualifier("logo");
    if(!$metadata) return '';
    return MidasLoader::loadModel("Bitstream")->load($metadata->getValue());
    }

   /* Set Logo
   * @param
   */
  function setLogo($value)
    {
    $this->setMetaDataByQualifier("logo", $value->getKey());
    }

  /** Get Submitter
   *
   * @return UserDao
   */
  function getSubmitter()
    {
    $metadata = $this->getMetaDataByQualifier("submitter");
    if(!$metadata) return false;
    return MidasLoader::loadModel("User")->load($metadata->getValue());
    }

   /* Set Submitter
   * @param
   */
  function setSubmitter($userDao)
    {
    if($userDao) $this->setMetaDataByQualifier("submitter", $userDao->getKey());
    }


  /**
   * Get Revision
   * @return ItemRevision
   */
  function getRevision()
    {
    if(!$this->_revision)  $this->_revision = $this->getModel()->getLastRevision($this);
    return $this->_revision;
    }

   /**
   * Check if is a revision (i.e. not the original submission, revision number > 1)
   * @return true or false
   */
    function isRevision()
      {
      try
        {
        $this->getModel()->getRevision($this, 1);  // First, check if any revisions exist
        }
      catch(Exception $e)
        {
        // New submission, no revisions exist yet
        return false;
        }

      try
        {
        $revisionNum = $this->getRevision()->getRevision();
        return $revisionNum != 1;  // Editing existing submission or revision
        }
      catch (Exception $e)
        {
        // New revision, hasn't been assigned a revision number yet
        return true;
        }
      }

  /**
   * Set Revision
   * @param ItemRevision
   */
  function setRevision($revision)
    {
    $this->_metadata = false;
    if($revision == "New")
      {
      Zend_Loader::loadClass('ItemRevisionDao', BASE_PATH.'/core/models/dao');
      $revision = new ItemRevisionDao();
      $revision->setChanges("");
      if(Zend_Registry::get('userSession') != null)
        {
        $revision->setUser_id(Zend_Registry::get('userSession')->Dao->getKey());
        }
      $revision->setDate(date('c'));
      $revision->setLicenseId(null);
      $lastRevision = false;
      if($this->saved) $lastRevision = $this->getModel()->getLastRevision($this);
      if(!$lastRevision)$this->_metadata = array();
      else $this->_metadata = MidasLoader::loadModel('ItemRevision')->getMetadata($lastRevision);
      }
    $this->_revision = $revision;
    }

  /**
   * Get Revision ID
   *
   * @return id
   */
  function getRevisionId()
    {
    $metadata = $this->getMetaDataByQualifier("revision_id");
    if(!$metadata)
      {
      $id = $this->getRevision()->getKey();
      $this->setRevisionId($id);
      return $id;
      }
    else
      {
      return $metadata->getValue();
      }
    }

   /**
   * Set Revision ID
   * @param id
   */
  function setRevisionId($id)
    {
    $this->setMetaDataByQualifier("revision_id", $id);
    }

  /**
   * Check if the user is an Admin
   * @param User
   * @return true or false
   */
  public function isAdmin($userDao)
    {
    if(!isset($userDao)) return null;
    if($userDao->isAdmin())
      {
      return true;
      }
    $issue =  end($this->getFolders());
    if(MidasLoader::loadModel("Folder")->policyCheck($issue, $userDao, MIDAS_POLICY_ADMIN)) // Issue Editor
      {
      return true;
      }
    $users = $this->getAdminGroup()->getUsers(); // Journal Editor
    foreach($users as $user)
      {
      if($user->getKey() == $userDao->getKey())
        {
        return true;
        }
      }
    return false;
    }

  /**
   * Get Resource Admin group
   * @return group
   */
  public function getAdminGroup()
    {
    $issue =  end($this->getFolders());
    $community =  MidasLoader::loadModel("Folder")->getCommunity(MidasLoader::loadModel("Folder")->getRoot($issue));
    return $community->getAdminGroup();
    }

  /**
   * Get Resource Member group
   * @return group
   */
  public function getMemberGroup()
    {
    $issue =  end($this->getFolders());
    $community =  MidasLoader::loadModel("Folder")->getCommunity(MidasLoader::loadModel("Folder")->getRoot($issue));
    return $community->getMemberGroup();
    }


  /**
   * Get Metadata object
   * @param type $type
   * @return type
   */
  function getMetaDataByQualifier($type)
    {
    $metadata = $this->getMetadata();
    return $this->_getMetaDataByQualifier($metadata, $type);
    }

  /**
   * Get Metadata object
   * @param type $type
   * @return array
   */
  function getAllMetaDataByQualifier($type,$level)
    {
    $lastRevision = $this->getRevision();
    try {
       $revisionNum = $lastRevision->getRevision();
    } catch (Exception $e) {
       $revisionNum=1;
    }
    if($revisionNum >0)
        {
        for($i = $revisionNum; $i > 0; $i--)
            {
            $searchReturn = '';
            $revision = $this->getModel()->getRevision($this, $i);
            if($revision)
                {
                $metadata = MidasLoader::loadModel('ItemRevision')->getMetadata($revision);
                $searchReturn =  $this->_getMetaDataByQualifier($metadata, $type);
                }
            if($searchReturn && (strpos($level,$searchReturn->getValue()) !== false))
                {
                return array($searchReturn,$i,$revision->getKey());
                }
            }
        }
    return array(false,0);
    }
  /**
   * Get Metadata object implementation
   * @param type $type
   * @return type
   */
  function _getMetaDataByQualifier($metadata, $type)
    {
    foreach($metadata as $m)
      {
      if($m->getQualifier() == $type)
        {
        return $m;
        }
      }
    return false;
    }
    
  /**
   * Save metadata value
   * @param string $type
   * @param string $value
   * @return MetadataDao
   */
  function setMetaDataByQualifier($type, $value)
    {
    // Gets the metadata
    $metadataDao = MidasLoader::loadModel('Metadata')->getMetadata(MIDAS_METADATA_TEXT, "journal", $type);
    if(!$metadataDao)  $metadataDao= MidasLoader::loadModel('Metadata')->addMetadata(MIDAS_METADATA_TEXT, "journal", $type, "");
    $metadataDao->setItemrevisionId($this->getRevision()->getKey());
    $metadataDao->setValue($value);
    return MidasLoader::loadModel('Metadata')->saveMetadataValue($metadataDao);
    }

  /**
   * Return all the item matadata (categories, keywords...)
   * @return array of metadata
   */
  private function getMetadata()
    {
    if($this->_metadata !== false) return $this->_metadata;
    if(!$this->saved) return array();
    $this->_metadata = MidasLoader::loadModel('ItemRevision')->getMetadata($this->getRevision());
    return $this->_metadata;
    }
  }
