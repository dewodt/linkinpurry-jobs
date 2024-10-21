<?php

namespace src\repositories;

use src\dao\{ApplicationDao, ApplicationStatus, JobDao, PaginationMetaDao, UserDao};
use src\database\Database;

class ApplicationRepository extends Repository
{
    // Dependency injection
    public function __construct(Database $db)
    {
        parent::__construct($db);
    }

    /**
     * Get a company's job applications (paginated)
     * @param int job_id
     * @param int page
     * @param int limit
     * @return ApplicationDao array
     */
    public function getJobApplications(int $job_id, int $page, int $limit): array
    {
        $queryMeta = "SELECT COUNT(*) FROM applications WHERE job_id = :job_id";
        $params = [
            ':job_id' => $job_id,
        ];

        // Get meta
        $totalItems = $this->db->queryOne($queryMeta, $params)[0];

        // Get data
        $offset = ($page - 1) * $limit;
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        $queryData = "
            SELECT * 
            FROM 
                applications 
                INNER JOIN jobs ON applications.job_id = jobs.job_id
                INNER JOIN users ON applications.user_id = users.id 
            WHERE jobs.job_id = :job_id 
            ORDER BY applications.created_at DESC 
            LIMIT :limit 
            OFFSET :offset";
        $result = $this->db->queryMany($queryData, $params);

        // Parse data
        $applications = [];
        foreach ($result as $raw) {
            $application = ApplicationDao::fromRaw($raw);
            $user = UserDao::fromRaw($raw);
            $job = JobDao::fromRaw($raw);
            $application->setUser($user);
            $application->setJob($job);

            $applications[] = $application;
        }

        // Parse meta
        $meta = new PaginationMetaDao($page, $limit, $totalItems);

        return [$applications, $meta];
    }

    /**
     * Get an application by id including the user and job
     * @param int application_id
     * @return ApplicationDao
     */
    public function getOneJobApplication(int $application_id): ApplicationDao | null
    {
        $query = "
            SELECT * 
            FROM 
                applications 
                INNER JOIN jobs ON applications.job_id = jobs.job_id
                INNER JOIN users ON applications.user_id = users.id 
            WHERE application_id = :application_id";
        $params = [
            ':application_id' => $application_id,
        ];

        $result = $this->db->queryOne($query, $params);

        if ($result === false) return null;

        $application = ApplicationDao::fromRaw($result);
        $user = UserDao::fromRaw($result);
        $job = JobDao::fromRaw($result);
        $application->setUser($user);
        $application->setJob($job);

        return $application;
    }

    /**
     * Update an application's status
     * @param ApplicationDao application
     * @return void
     */
    public function updateApplicationStatus(ApplicationDao $application): void
    {
        $query = "UPDATE applications SET status = :status, status_reason = :status_reason WHERE application_id = :application_id";
        $params = [
            ':application_id' => $application->getApplicationId(),
            ':status' => $application->getStatus()->value,
            ':status_reason' => $application->getStatusReason(),
        ];

        $this->db->executeUpdate($query, $params);

        return;
    }
}
