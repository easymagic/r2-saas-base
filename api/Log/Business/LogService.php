<?php

namespace Log\Business;

use Exception;
use Shared\AbstractBaseService;
use Log\Data\LogRepositoryInterface;
use Log\Data\LogEntity;
use Log\Data\LogMigrationRepositoryInterface;

/**
 * @extends AbstractBaseService<LogEntity, LogRepositoryInterface>
 */
class LogService extends AbstractBaseService implements LogServiceInterface
{
    private LogMigrationRepositoryInterface $logMigrationRepositoryInterface;
    private LogRepositoryInterface $logRepository;

    public function __construct(
        LogMigrationRepositoryInterface $logMigrationRepositoryInterface,
        LogRepositoryInterface $logRepository
    ) {
        parent::__construct($logRepository);
        $this->logMigrationRepositoryInterface = $logMigrationRepositoryInterface;
        $this->logRepository = $logRepository;
    }

    public function migrate()
    {
        return $this->logMigrationRepositoryInterface->migrate();
    }

    /**
     * @param array $filters
     * @return \Shared\Query\QueryObject<LogEntity>
     */
    public function fetchLogs(array $filters = [])
    {
        return $this->logRepository->query($filters);
    }

    /**
     * @param string $title
     * @param string $payload
     * @param string $response
     * @param string $type success|error
     * @return LogEntity
     */
    public function createLog(string $title, string $payload, string $response, string $type)
    {
        if ($title === '') {
            throw new Exception('Title is required');
        }

        $type = strtolower(trim($type));
        if ($type !== 'success' && $type !== 'error' && $type !== 'info') {
            throw new Exception('Type must be success or error or info');
        }

        return $this->logRepository->save(0, [
            'title' => $title,
            'payload' => $payload,
            'response' => $response,
            'type' => $type,
        ]);
    }
}
