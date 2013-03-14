<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Codeigniter Softlayer DNS
*
* Authentication and Permissions System
*
* @author Prasetyo Wicaksono <prasetyo@nanmit.es>
* @license DBAD <http://dbad-license.org/license>
* @link https://github.com/Atriedes/codeigniter-softlayer-dns
* @todo Add batch add and remove dns records
*/

require_once APPPATH."third_party/SoftLayer/XmlrpcClient.class.php";

class CI_Softlayer extends Softlayer_XmlrpcClient
{

	/**
	* Setting for your api username and api username
	*/
	private $userName = 'your-api-username';
	private $apiKey = 'your-api-key';

	/**
	* Setting default NS
	*/
	private $ns1 = 'ns1.nanomit.es';
	private $ns2 = 'ns2.nanomit.es';

	/**
	 * Get domain by it's id or name
	 *
	 * @access	private
	 * @param	integer
	 * @param 	string
	 * @return	integer
	 */
	private function getDomainId($domainId = 0, $domainName = '')
	{
		if($domainId != 0)
		{
			return $domainId;
		} else if($domainName != '')
		{
			$domain = $this->searchDomain($domainName);

			if(empty($domain))
			{
				return FALSE;
			}

			foreach($domain as $row)
			{
				return $row->id;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Search domain by keyword
	 *
	 * @access	public
	 * @param	string
	 * @return	array
	 */
	public function searchDomain($keyword = '')
	{
		$client = parent::getClient('SoftLayer_Dns_Domain', null, $this->userName, $this->apiKey);

		try {

			$result = $client->getByDomainName($keyword);
			return array('status' => TRUE, 'msg' => 'searchDomain has performed successfully','result' => $result);

		} catch (Exception $e)
		{

			return array('status' => FALSE, 'msg' => $e->getMessage, 'result' => NULL)
		
		}
	}

	/**
	 * Add new domain
	 *
	 * @access	public
	 * @param	string
	 * @param 	string
	 * @param 	integer
	 * @return	array
	 */
	public function addDomain($domainName = '', $ip = '', $ttl = 86400)
	{
		$client = parent::getClient('SoftLayer_Dns_Domain', null, $this->userName, $this->apiKey);

		try {

			// Make new object for parameter
			$param = new stdClass();

			// Fill name parameter
			$param->name = $domainName;

			// Fill default dns record
			$param->resourceRecords = array();
			$param->resourceRecords[0] = new stdClass();
			$param->resourceRecords[0]->host = '@';
			$param->resourceRecords[0]->data = $ip;
			$param->resourceRecords[0]->type = 'a';
			$param->resourceRecords[0]->ttl = $ttl;

			$param->resourceRecords[1] = new stdClass();
			$param->resourceRecords[1]->host = '@';
			$param->resourceRecords[1]->data = $this->ns1;
			$param->resourceRecords[1]->type = 'ns';
			$param->resourceRecords[1]->ttl = $ttl;

			$param->resourceRecords[2] = new stdClass();
			$param->resourceRecords[2]->host = '@';
			$param->resourceRecords[2]->data = $this->ns2;
			$param->resourceRecords[2]->type = 'ns';
			$param->resourceRecords[2]->ttl = $ttl;

			$result = $client->createObject($param);

			return array('status' => TRUE, 'msg' => 'addDomain has performed successfully', 'result' => $result);

		} catch (Exception $e)
		{

			return array('status' => FALSE, 'msg' => $e->getMessage, 'result' => NULL)

		}
	}

	/**
	 * Revome domain by its id or its name
	 *
	 * @access	public
	 * @param	integer
	 * @param 	string
	 * @return	array
	 */
	public function removeDomain($domainId = 0, $domainName = '')
	{
		// Get domain id
		$id = $this->getDomainId($domainId,$domainName);
		
		if($id === FALSE)
		{
			return array('status' => FALSE, 'msg' => 'domainId or domainName cannot be NULL', 'result' => NULL)
		}

		$client = parent::getClient('SoftLayer_Dns_Domain', $id, $this->userName, $this->apiKey);

		try{

			$result = $client->deleteObject();
			return array('status' => TRUE, 'msg' => 'removeDomain has performed successfully', 'result' => $result);
		
		} catch(Exception $e)
		{
			return array('status' => FALSE, 'msg' => $e->getMessage, 'result' => NULL)
		}
	}

	/**
	 * Retrieve all records for specific domain
	 *
	 * @access	public
	 * @param	integer
	 * @param 	string
	 * @return	array
	 */
	public function retrieveRecords($domainId = 0, $domainName = '')
	{
		// Get domain id
		$id = $this->getDomainId($domainId,$domainName);

		if($id === FALSE)
		{
			return array('status' => FALSE, 'msg' => 'domainId or domainName cannot be NULL', 'result' => NULL)
		}

		$client = parent::getClient('SoftLayer_Dns_Domain', $id, $this->userName, $this->apiKey);

		try {

			$result = $client->getResourceRecords();
			return array('status' => TRUE, 'msg' => 'retrieveRecords has performed successfully', 'result' => $result);

		} catch (Exception $e)
		{

			return array('status' => FALSE, 'msg' => $e->getMessage, 'result' => NULL)

		}
	}

	/**
	 * Add new dns record
	 *
	 * @access	public
	 * @param	integer
	 * @param 	string
	 * @param 	string
	 * @param 	string
	 * @param 	string
	 * @param 	integer
	 * @param 	integer
	 * @return	array
	 */
	public function addRecord($domainId = 0, $domainName = '', $type = 'a', $host = '', $data = '', $ttl = 86400, $mxPriority = NULL )
	{
		// Get domain id
		$id = $this->getDomainId($domainId,$domainName);

		if($id === FALSE)
		{
			return array('status' => FALSE, 'msg' => 'domainId or domainName cannot be NULL', 'result' => NULL)
		}

		$client = parent::getClient('SoftLayer_Dns_Domain', $id, $this->userName, $this->apiKey);

		try
		{
			// Switch dns type record
			switch ($type)
			{
				case 'a':
					$result = $client->createARecord($host, $data, $ttl);
					break;
				case 'txt':
					$result = $client->createTxtRecord($host, $data, $ttl);
					break;
				case 'cname':
					$result = $client->createCnameRecord($host, $data, $ttl);
					break;
				case 'aaaa':
					$result = $client->createAaaaRecord($host, $data, $ttl);
					break;
				case 'mx':
					$result = $client->createMxRecord($host, $data, $ttl, $mxPriority);
					break;
				case 'ns':
					$result = $client->createNsRecord($host, $data, $ttl);
					break;
				case 'ptr':
					$result = $client->createPtrRecord($host, $data, $ttl);
					break;
				case 'spf':
					$result = $client->createSpfRecord($host, $data, $ttl);
					break;
				default:
					return FALSE;
					break;
			}
		} catch (Exception $e)
		{
			return array('status' => FALSE, 'msg' => $e->getMessage, 'result' => NULL)
		}

		return array('status' => TRUE, 'msg' => 'addRecord has performed successfully', 'result' => $result);
	}

	/**
	 * Add srv record to specific domain
	 *
	 * @access	public
	 * @param	integer
	 * @param 	string
	 * @param 	string
	 * @param 	string
	 * @param 	string
	 * @param 	integer
	 * @param 	integer
	 * @param 	string
	 * @param 	integer
	 * @return	array
	 */
	public function addSrvRecord($domainId = 0, $domainName = '', $host = '', $data = '', $protocol = 'TCP', $port = 0, $priority = 0, $service = '', $ttl = 86400)
	{

		//Get domain id
		$id = $this->getDomainId($domainId,$domainName);

		if($id === FALSE)
		{
			return array('status' => FALSE, 'msg' => 'domainId or domainName cannot be NULL', 'result' => NULL)
		}

		$client = parent::getClient('SoftLayer_Dns_Domain_ResourceRecord_SrvType', NULL, $this->userName, $this->apiKey);

		try{

			// Create object and fill parameter
			$param = new stdClass();
			$param->data = $data;
			$param->domainId = $id;
			$param->host = $host;
			$param->port = $port;
			$param->priority = $priority;
			$param->protocol = $protocol;
			$param->service = $service;
			$param->weight = $weight;
			$param->ttl = $ttl;
			$param->type = 'srv';

			$result = $client->createObject($param);
			return array('status' => TRUE, 'msg' => 'addRecord has performed successfully', 'result' => $result);

		}catch(Exception $e)
		{

			return array('status' => FALSE, 'msg' => $e->getMessage, 'result' => NULL)

		}
		
	}

	/**
	 * Remove dns record by its record id
	 *
	 * @access	public
	 * @param	integer
	 * @return	array
	 */
	public function removeRecord($recordId = 0)
	{
		if($recordId == 0)
		{
			return array('status' => FALSE, 'msg' => 'recordId cannot be NULL', 'result' => NULL);
		}

		$client = parent::getClient('SoftLayer_Dns_Domain_ResourceRecord', $recordId, $this->userName, $this->apiKey);

		try{

			$result = $client->deleteObject();
			return array('status' => TRUE, 'msg' => 'removeRecord has performed successfully', 'result' => $result);

		} catch(Exception $e)
		{

			return array('status' => FALSE, 'msg' => $e->getMessage, 'result' => NULL)

		}
	}

	/**
	 * Edit dns record by its record id
	 *
	 * @access	public
	 * @param	integer
	 * @param 	string
	 * @param 	string
	 * @param 	integer
	 * @param 	integer
	 * @return	array
	 */
	public function editRecord($recordId = 0, $host = '', $data = '', $ttl = 86400, $mxPriority = NULL)
	{
		$client = parent::getClient('SoftLayer_Dns_Domain_ResourceRecord', $recordId, $this->userName, $this->apiKey);

		try{

			// Create object and fill parameter
			$param = new stdClass();
			$param->host = $host;
			$param->data = $data;
			$param->ttl = $ttl;
			$param->mxPriority = $mxPriority;

			$result = $client->editObject($param);
			return array('status' => TRUE, 'msg' => 'removeRecord has performed successfully', 'result' => $result);

		}catch(Exception $e)
		{

			return array('status' => FALSE, 'msg' => $e->getMessage, 'result' => NULL)

		}
	}

	/**
	 * Edit srv dns record by its record id
	 *
	 * @access	public
	 * @param	integer
	 * @param 	string
	 * @param 	string
	 * @param 	string
	 * @param 	integer
	 * @param 	integer
	 * @param 	string
	 * @param 	integer
	 * @return	array
	 */
	public function editSrvRecord($recordId = 0, $host = '', $data = '', $protocol = 'TCP', $port = 0, $priority = 0, $service = '', $ttl = 86400)
	{
		$client = parent::getClient('SoftLayer_Dns_Domain_ResourceRecord_SrvType', NULL, $this->userName, $this->apiKey);

		try{

			// Create object and fill parameter
			$param = new stdClass();
			$param->data = $data;
			$param->host = $host;
			$param->port = $port;
			$param->priority = $priority;
			$param->protocol = $protocol;
			$param->service = $service;
			$param->weight = $weight;
			$param->ttl = $ttl;
			$param->type = 'srv';

			$resull = $client->editObject($param);
			return array('status' => TRUE, 'msg' => 'removeRecord has performed successfully', 'result' => $result);

		}catch(Exception $e)
		{
			return array('status' => FALSE, 'msg' => $e->getMessage, 'result' => NULL)
		}
	}
}