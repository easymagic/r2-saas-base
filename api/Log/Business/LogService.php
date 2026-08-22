<?php

namespace Log\Business;

use Shared\AbstractBaseService;
use Log\Business\Dtos\CreateLogDto;
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

    public function fetchLogs(array $filters = [])
    {
        return $this->logRepository->query($filters);
    }

    public function createLog(CreateLogDto $createLogDto)
    {
        return $this->logRepository->save(new LogEntity([
            'title' => $createLogDto->title,
            'payload' => $createLogDto->payload,
            'response' => $createLogDto->response,
            'type' => $createLogDto->type,
        ]));
    }
}
