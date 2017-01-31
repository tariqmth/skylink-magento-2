<?php

namespace RetailExpress\SkyLink\Model\Outlets;

use Magento\Framework\App\ResourceConnection;
use RetailExpress\SkyLink\Api\Outlets\SkyLinkOutletRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;
use RetailExpress\SkyLink\Sdk\Outlets\OutletId as SkyLinkOutletId;

class SkyLinkOutletRepository implements SkyLinkOutletRepositoryInterface
{
    private $connection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

    /**
     * @return RetailExpress\SkyLink\Sdk\Outlets\Outlet
     */
    public function getList()
    {
        $rows = $this->connection->fetchAssoc(
            $this->connection
                ->select()
                ->from($this->getOutletsTable())
        );

        return array_map(function (array $row) {
            return $this->buildOutlet($row);
        }, $rows);
    }

    public function save(SkyLinkOutlet $skyLinkOutlet)
    {
        $skyLinkOutletId = $skyLinkOutlet->getId();
        $databasePayload = $this->getDatabasePayload($skyLinkOutlet);

        if ($this->outletExists($skyLinkOutletId)) {
            $result = $this->connection->update(
                $this->getOutletsTable(),
                $databasePayload,
                ['id = ?' => $skyLinkOutletId]
            );
        } else {
            $databasePayload = array_merge($databasePayload, [
                'id' => $skyLinkOutletId,
            ]);

            $this->connection->insert($this->getOutletsTable(), $databasePayload);
        }
    }

    private function buildOutlet(array $row)
    {
        return SkyLinkOutlet::fromNative(
            $row['id'],
            $row['name'],
            (string) $row['address_line_1'],
            (string) $row['address_line_2'],
            (string) $row['address_line_2'],
            (string) $row['address_city'],
            (string) $row['address_state'],
            (string) $row['address_postcode'],
            (string) $row['address_country_code'],
            (string) $row['phone_number'],
            (string) $row['fax_number']
        );
    }

    private function getDatabasePayload(SkyLinkOutlet $skyLinkOutlet)
    {
        $payload = [
            'name' => $skyLinkOutlet->getName(),
            'address_line_1' => $skyLinkOutlet->getAddress()->getLine1(),
            'address_line_2' => $skyLinkOutlet->getAddress()->getLine2(),
            'address_line_3' => $skyLinkOutlet->getAddress()->getLine3(),
            'address_city' => $skyLinkOutlet->getAddress()->getCity(),
            'address_state' => $skyLinkOutlet->getAddress()->getState(),
            'address_postcode' => $skyLinkOutlet->getAddress()->getPostcode(),
            'phone_number' => $skyLinkOutlet->getPhoneNumber(),
            'fax_number' => $skyLinkOutlet->getFaxNumber(),
        ];

        $country = $skyLinkOutlet->getAddress()->getCountry();

        if (null !== $country) {
            $payload['address_country_code'] = $country->getCode();
        }

        // Change empty strings to null values to keep our database clean
        return array_map(function ($value) {
            if ('' === (string) $value) {
                return null;
            }

            return $value;
        }, $payload);
    }

    private function outletExists(SkyLinkOutletId $skyLinkOutletId)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getOutletsTable())
                ->where('id = ?', $skyLinkOutletId)
        );
    }

    private function getOutletsTable()
    {
        return $this->connection->getTableName('retail_express_skylink_outlets');
    }
}
