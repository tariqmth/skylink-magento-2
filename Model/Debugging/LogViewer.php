<?php

namespace RetailExpress\SkyLink\Model\Debugging;

use DateTimeImmutable;
use RetailExpress\SkyLink\Api\Debugging\LogViewerInterface;

class LogViewer implements LogViewerInterface
{
    use LogHelper;

    /**
     * {@inheritdoc}
     */
    public function getList($sinceId = null)
    {
        $query = $this->getConnection()->select()
                ->from($this->getLogsTable());

        if (null !== $sinceId) {
            $query->where('id > ?', $sinceId);
        }

        $result = $this->getConnection()->fetchAll($query);

        return array_map(function (array $row) {
            $row['context'] = json_decode($row['context'], true);
            $row['logged_at'] = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['logged_at']);
            $row['captured'] = (bool) $row['captured'];

            return $row;
        }, $result);
    }
}
