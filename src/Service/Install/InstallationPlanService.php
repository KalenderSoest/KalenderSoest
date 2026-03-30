<?php

namespace App\Service\Install;

final class InstallationPlanService
{
    /**
     * @param array<string, mixed> $state
     * @return array<int, array<string, mixed>>
     */
    public function build(array $state): array
    {
        $mode = $state['mode'] ?? 'repair_config';
        $checks = $state['database']['checks'] ?? [];
        $schemaReady = (bool) ($state['database']['schema_ready'] ?? false);
        $schemaUpdatePending = (bool) ($state['database']['schema_update_pending'] ?? false);
        $kundenCount = (int) ($state['database']['kunden_count'] ?? 0);
        $legacyMediaColumnsExist = ($checks['legacy_media']['existing_columns'] ?? []) !== [];
        $pendingMigration = $state['migrations']['latest_pending'] ?? null;

        $steps = [
            [
                'title' => 'Codebasis prüfen',
                'description' => 'Prüft composer.json, composer.lock und vendor/autoload.php.',
                'done' => (bool) ($state['composer']['vendor_autoload_exists'] ?? false),
                'action_route' => null,
                'action_label' => null,
            ],
            [
                'title' => 'Umgebung prüfen',
                'description' => 'Liest .env/.env.local und prüft DATABASE_URL sowie MAILER_DSN.',
                'done' => ($state['env']['database_url'] ?? null) !== null,
                'action_route' => null,
                'action_label' => null,
            ],
            [
                'title' => 'Datenbank prüfen',
                'description' => 'Prüft DB-Verbindung und erkennt, ob Tabellen vorhanden sind.',
                'done' => (bool) ($state['database']['connectable'] ?? false),
                'action_route' => null,
                'action_label' => null,
            ],
        ];

        if ($mode === 'fresh_install' || $mode === 'fresh_install_pending_setup') {
            $steps[] = [
                'title' => 'Schema installieren',
                'description' => 'Leere Datenbank anhand des aktuellen Schemas initial anlegen.',
                'done' => $schemaReady,
                'action_route' => $schemaReady ? null : 'dfx_install_run_step',
                'action_params' => $schemaReady ? [] : ['step' => 'schema_migrate'],
                'action_label' => $schemaReady ? null : 'Schema anlegen',
            ];
            $steps[] = [
                'title' => 'Basisdaten anlegen',
                'description' => 'Webuser, Kunde, Account und Standardkonfiguration anlegen.',
                'done' => $kundenCount > 0,
                'action_route' => $schemaReady && $kundenCount === 0 ? 'dfx_install4' : null,
                'action_label' => $schemaReady && $kundenCount === 0 ? 'Installationswizard fortsetzen' : null,
                'hint' => $schemaReady ? null : 'Wird erst nach erfolgreicher Schema-Installation freigeschaltet.',
            ];
        } elseif ($mode === 'migrate_existing') {
            if (($checks['array_json']['needed'] ?? false) === true) {
                $steps[] = [
                    'title' => 'Array-Felder auf JSON migrieren',
                    'description' => (string) ($checks['array_json']['message'] ?? ''),
                    'done' => false,
                    'action_route' => 'dfx_install_run_step',
                    'action_params' => ['step' => 'migrate_array_json'],
                    'action_label' => 'JSON-Migration ausführen',
                ];
            }
            if (($checks['to_group']['needed'] ?? false) === true && ($checks['array_json']['needed'] ?? false) !== true) {
                $steps[] = [
                    'title' => 'toGroup migrieren',
                    'description' => (string) ($checks['to_group']['message'] ?? ''),
                    'done' => false,
                    'action_route' => 'dfx_install_run_step',
                    'action_params' => ['step' => 'migrate_konf_to_group'],
                    'action_label' => 'toGroup migrieren',
                ];
            }
            if (($checks['legacy_media']['needed'] ?? false) === true) {
                $steps[] = [
                    'title' => 'Legacy-Medien übernehmen',
                    'description' => (string) ($checks['legacy_media']['message'] ?? ''),
                    'done' => false,
                    'action_route' => 'dfx_install_run_step',
                    'action_params' => ['step' => 'legacy_termin_media'],
                    'action_label' => 'Legacy-Medien migrieren',
                ];
            }
            $blockingLegacyMigrations = (bool) ($checks['array_json']['needed'] ?? false)
                || (bool) ($checks['to_group']['needed'] ?? false)
                || (bool) ($checks['legacy_media']['needed'] ?? false);
            $schemaStepPending = $schemaUpdatePending || $legacyMediaColumnsExist;
            $steps[] = [
                'title' => 'Migration erzeugen',
                'description' => 'Erzeugt eine individuelle Update-Migration für diese Installation.',
                'done' => $pendingMigration !== null || (!$schemaStepPending && !$blockingLegacyMigrations),
                'action_route' => $schemaStepPending && !$blockingLegacyMigrations && $pendingMigration === null ? 'dfx_install_run_step' : null,
                'action_params' => $schemaStepPending && !$blockingLegacyMigrations && $pendingMigration === null ? ['step' => 'generate_update_migration'] : [],
                'action_label' => $schemaStepPending && !$blockingLegacyMigrations && $pendingMigration === null ? 'Migration erzeugen' : null,
                'hint' => $blockingLegacyMigrations ? 'Wird erst nach den vorgeschalteten Legacy-Migrationen freigeschaltet.' : null,
            ];
            $steps[] = [
                'title' => 'Migration prüfen',
                'description' => 'Zeigt die erzeugte Migrationsdatei vor der Ausführung an.',
                'done' => !$schemaStepPending && $pendingMigration === null && !$blockingLegacyMigrations,
                'action_route' => $pendingMigration !== null ? 'dfx_install_run_step' : null,
                'action_params' => $pendingMigration !== null ? ['step' => 'review_update_migration'] : [],
                'action_label' => $pendingMigration !== null ? 'Migration prüfen' : null,
                'hint' => $pendingMigration === null && ($schemaStepPending || $blockingLegacyMigrations) ? 'Wird nach Erzeugung der Migration freigeschaltet.' : null,
            ];
            $steps[] = [
                'title' => 'Migration ausführen',
                'description' => 'Führt die individuell erzeugte Update-Migration aus.',
                'done' => !$schemaStepPending && $pendingMigration === null && !$blockingLegacyMigrations,
                'action_route' => $pendingMigration !== null ? 'dfx_install_run_step' : null,
                'action_params' => $pendingMigration !== null ? ['step' => 'apply_update_migration'] : [],
                'action_label' => $pendingMigration !== null ? 'Migration ausführen' : null,
                'hint' => $pendingMigration === null && ($schemaStepPending || $blockingLegacyMigrations) ? 'Wird nach Prüfung der Migration freigeschaltet.' : null,
            ];
            if (($checks['legacy_passwords']['needed'] ?? false) === true) {
                $steps[] = [
                    'title' => 'Legacy-Passwörter prüfen',
                    'description' => (string) ($checks['legacy_passwords']['message'] ?? ''),
                    'done' => false,
                    'action_route' => 'dfx_install_run_step',
                    'action_params' => ['step' => 'audit_legacy_passwords'],
                    'action_label' => 'Passwort-Audit anzeigen',
                ];
            }
            $steps[] = [
                'title' => 'Produktivcache leeren',
                'description' => 'Nach dem Update den produktiven Cache neu aufbauen.',
                'done' => false,
                'action_route' => 'dfx_install_run_step',
                'action_params' => ['step' => 'clear_prod_cache'],
                'action_label' => 'Cache leeren',
            ];
        } elseif ($mode === 'repair_config') {
            $steps[] = [
                'title' => 'Konfiguration reparieren',
                'description' => 'Zuerst .env und Datefix-Konfiguration vervollständigen oder korrigieren.',
                'done' => false,
                'action_route' => null,
                'action_label' => null,
            ];
        } elseif ($mode === 'missing_vendor') {
            $steps[] = [
                'title' => 'Abhängigkeiten installieren',
                'description' => 'Vor jedem weiteren Schritt composer install --no-dev --optimize-autoloader ausführen.',
                'done' => false,
                'action_route' => null,
                'action_label' => null,
            ];
        }

        return $steps;
    }
}
