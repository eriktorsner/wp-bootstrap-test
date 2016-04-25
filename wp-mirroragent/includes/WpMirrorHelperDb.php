<?php

class WpMirrorHelperDb
{
    /**
     * @var int
     */
    private $qryLimit = 500;

    public function __construct()
    {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    public function getTables()
    {
        $tableArray = $this->getTablesArray();
        $ret = join("\n", $tableArray);

        return $ret;
    }

    public function packTable($table)
    {
        // sanity check input
        $tableArray = $this->getTablesArray();
        if (!in_array($table, $tableArray)) {
            return false;
        }


        $zipFile = $this->dumpTable($table);
        return $zipFile;

    }

    private function getTablesArray()
    {
        global $wpdb;
        $sql = "SHOW TABLES LIKE '{$wpdb->prefix}%'";
        $results = $wpdb->get_results($sql);
        $ret = array();
        foreach ($results as $index => $value) {
            $tableName = reset($value);
            $ret[$tableName] = $tableName;
        }

        return $ret;
    }

    private function dumpTable($table)
    {
        global $wpdb;

        $fileName = wp_tempnam();
        $handle = fopen($fileName, 'w');

        $create = $wpdb->get_row("SHOW CREATE TABLE `{$table}`", ARRAY_N);
        @fwrite($handle, "{$create[1]};\n\n");
        $rowCount = $wpdb->get_var("SELECT Count(*) FROM `{$table}`");

        if ($rowCount > $this->qryLimit) {
            $batches = ceil($rowCount / $this->qryLimit);
        } else {
            if ($rowCount > 0) {
                $batches = 1;
            }
        }

        for ($i = 0; $i < $batches; $i++) {
            $sql = "";
            $limit = $i * $this->qryLimit;
            $query = "SELECT * FROM `{$table}` LIMIT {$limit}, {$this->qryLimit}";
            $rows = $wpdb->get_results($query, ARRAY_A);

            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $sql .= "INSERT INTO `{$table}` VALUES(";
                    $values = array();
                    foreach ($row as $value) {
                        if (is_null($value) || !isset($value)) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = '"' . @esc_sql($value) . '"';
                        }
                    }
                    $sql .= join(',', $values);
                    $sql .= ");\n";
                }
                fwrite($handle, $sql);
            }
        }

        $sql = "\nSET FOREIGN_KEY_CHECKS = 1; \n\n";
        fwrite($handle, $sql);

        fclose($handle);

        $zip = new \ZipArchive();
        $zipFile = wp_tempnam();
        $zip->open($zipFile, \ZipArchive::OVERWRITE);
        $zip->addFile($fileName, 'sql');
        $zip->close();
        unlink($fileName);

        return $zipFile;
    }

}