<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH."third_party/SoftLayer/XmlrpcClient.class.php";

class CI_Softlayer extends Softlayer_XmlrpcClient
{

	private $userName = 'jowy';
	private $apiKey = 'e3c3dd15ffc190d4727ecf7b1a2aacb5149b0ced139a2dfad43218338b6aa0f0';

	private $ns1 = 'ns1.nanomit.es';
	private $ns2 = 'ns2.nanomit.es';

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

	public function searchDomain($keyword)
	{
		$domain = parent::getClient('SoftLayer_Dns_Domain', null, $this->userName, $this->apiKey);

		try {
			return $domain->getByDomainName($keyword);
		} catch (Exception $e)
		{
			die('Error : ' . $e->getMessage());
		}
	}

	public function addDomain($domainName, $ip, $ttl = 86400)
	{
		$domain = parent::getClient('SoftLayer_Dns_Domain', null, $this->userName, $this->apiKey);

		try {
			$param = new stdClass();
			$param->name = $domainName;
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

			return $domain->createObject($param);

		} catch (Exception $e)
		{
			die('Error : ' . $e->getMessage());
		}
	}

	public function removeDomain($domainId = 0, $domainName = '')
	{
		
		$id = $this->getDomainId($domainId,$domainName);
		
		$client = parent::getClient('SoftLayer_Dns_Domain', $id, $this->userName, $this->apiKey);

		try{
			return $client->deleteObject();
		} catch(Exception $e)
		{
			die('Error : ' . $e->getMessage());
		}
	}

	public function retrieveRecords($domainID = 0, $domainName = '')
	{
		$id = $this->getDomainId($domainId,$domainName);

		$client = parent::getClient('SoftLayer_Dns_Domain', $id, $this->userName, $this->apiKey);

		try {
			return $client->getResourceRecords();
		} catch (Exception $e)
		{
			die('Error : ' . $e->getMessage());
		}
	}

	public function addRecord($domainID = 0, $domainName = '', $type = 'a', $host = '', $data = '', $ttl = 86400, $mxPriority = 0 )
	{

		$id = $this->getDomainId($domainId,$domainName);

		$client = parent::getClient('SoftLayer_Dns_Domain', $id, $this->userName, $this->apiKey);

		try
		{
			switch ($type)
			{
				case 'a':
					return $client->createARecord($host, $data, $ttl);
					break;
				case 'txt':
					return $client->createTxtRecord($host, $data, $ttl);
					break;
				case 'cname':
					return $client->createCnameRecord($host, $data, $ttl);
					break;
				case 'aaaa':
					return $client->createAaaaRecord($host, $data, $ttl);
					break;
				case 'mx':
					return $client->createMxRecord($host, $data, $ttl, $mxPriority);
					break;
				case 'ns':
					return $client->createNsRecord($host, $data, $ttl);
					break;
				case 'ptr':
					return $client->createPtrRecord($host, $data, $ttl);
					break;
				case 'spf':
					return $client->createSpfRecord($host, $data, $ttl);
					break;
				default:
					return FALSE;
					break;
			}
		} catch (Exception $e)
		{
			die('Error : ' . $e->getMessage());
		}
	}

	public function addSrvRecord($domainId = 0, $domainName = '', $host = '', $data = '', $protocol = 'TCP', $port = 0, $priority = 0, $service = '', $ttl = 86400)
	{
		$id = $this->getDomainId($domainId,$domainName);

		$client = parent::getClient('SoftLayer_Dns_Domain_ResourceRecord_SrvType', NULL, $this->userName, $this->apiKey);

		try{
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

			return $client->createObject($param);
		}catch(Exception $e)
		{
			die('Error : ' . $e->getMessage());
		}
		
	}

	public function removeRecord($recordId = 0)
	{
		if($recordId == 0)
		{
			return FALSE;
		}

		$client = parent::getClient('SoftLayer_Dns_Domain_ResourceRecord', $recordId, $this->userName, $this->apiKey);

		try{
			return $client->deleteObject();
		} catch(Exception $e)
		{
			die('Error : ' . $e->getMessage());
		}
	}

	public function editRecord($recordId = 0, $host = '', $data = '', $ttl = 86400, $mxPriority = NULL)
	{
		$client = parent::getClient('SoftLayer_Dns_Domain_ResourceRecord', $recordId, $this->userName, $this->apiKey);

		try{
			$param = new stdClass();
			$param->host = $host;
			$param->data = $data;
			$param->ttl = $ttl;
			$param->mxPriority = $mxPriority;

			return $client->editObject($param);
		}catch(Exception $e)
		{
			die('Error : ' . $e->getMessage());
		}
	}

	public function editSrvRecord($recordId = 0, $host = '', $data = '', $protocol = 'TCP', $port = 0, $priority = 0, $service = '', $ttl = 86400)
	{
		$client = parent::getClient('SoftLayer_Dns_Domain_ResourceRecord_SrvType', NULL, $this->userName, $this->apiKey);

		try{
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

			return $client->editObject($param);
		}catch(Exception $e)
		{
			die('Error : ' . $e->getMessage());
		}
	}
}