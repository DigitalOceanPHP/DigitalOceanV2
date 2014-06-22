<?php

/**
 * This file is part of the DigitalOceanV2 library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DigitalOceanV2\Api;

use DigitalOceanV2\Entity\DomainRecord as DomainRecordEntity;

/**
 * @author Yassir Hannoun <yassir.hannoun@gmail.com>
 */
class DomainRecord extends AbstractApi
{
    /**
     * @param string                 $domainName
     * @return DomainRecordEntity[]
     */
    public function getAll($domainName)
    {
        $domainRecords = $this->adapter->get(sprintf("%s/domains/%s/records", self::ENDPOINT, $domainName));
        $domainRecords = json_decode($domainRecords);

        $results = array();
        foreach ($domainRecords->domain_records as $domainRecord) {
            $results[] = new DomainRecordEntity($domainRecord);
        }

        return $results;
    }

    /**
     * @param string                $domainName
     * @param integer               $id
     * @return DomainRecordEntity
     */
    public function getById($domainName, $id)
    {
        $domainRecords = $this->adapter->get(sprintf("%s/domains/%s/records/%d", self::ENDPOINT, $domainName, $id));
        $domainRecords = json_decode($domainRecords);

        return new DomainRecordEntity($domainRecords->domain_record);
    }

    /**
     * @param string                $domainName
     * @param string                $type
     * @param string                $name
     * @param string                $data
     * @param string|null           $priority
     * @param string|null           $port
     * @param string|null           $weight
     * @throws \RuntimeException
     * @return DomainRecordEntity
     */
    public function create($domainName, $type, $name, $data, $priority = NULL, $port = NULL, $weight = NULL)
    {
        $headers = array('Content-Type: application/json');
        $content = "";
        if($type === "A" || $type === "AAAA" || $type === "CNAME"|| $type === "TXT")
        {
            $content .= sprintf('{"name":"%s", "type":"%s", "data":"%s"}', $name, $type, $data);
        }
        else if($type === "NS")
        {
            $content .= sprintf('{"type":"%s", "data":"%s"}', $type, $data);
        }
        else if($type === "MX")
        {
            if($priority === NULL)
            {
                $content .= sprintf('{"type":"%s", "data":"%s"}', $type, $data);
            }
            else
            {
                $content .= sprintf('{"type":"%s", "data":"%s", "priority":"%s"}', $type, $data, $priority);
            }
        }
        else if($type === "SRV")
        {
            $content .= sprintf('{"name":"%s", "type":"%s", "data":"%s", "priority":%d, "port":%d, "weight":%d}', $name, $type, $data, $priority, $port, $weight);
        }
        else
        {
            throw new \RuntimeException("Domain record type is invalid");
        }

        $domainRecord = $this->adapter->post(sprintf("%s/domains/%s/records", self::ENDPOINT, $domainName), $headers, $content);
        $domainRecord = json_decode($domainRecord);

        return new DomainRecordEntity($domainRecord->domain_record);
    }

    /**
     * @param string                $domainName
     * @param integer               $recordId
     * @param string                $name
     * @throws \RuntimeException
     * @return DomainRecordEntity
     */
    public function update($domainName, $recordId, $name)
    {
        $headers = array('Content-Type: application/json');
        $content = sprintf('{"name":"%s"}', $name);

        $domainRecord = $this->adapter->put(sprintf("%s/domains/%s/records/%d", self::ENDPOINT, $domainName, $recordId), $headers, $content);
        $domainRecord = json_decode($domainRecord);

        return new DomainRecordEntity($domainRecord->domain_record);
    }

    /**
     * @param string                $domainName
     * @param integer               $recordId
     */
    public function delete($domainName, $recordId)
    {
        $headers = array('Content-Type: application/x-www-form-urlencoded');
        $this->adapter->delete(sprintf("%s/domains/%s/records/%d", self::ENDPOINT, $domainName, $recordId), $headers);
    }
}