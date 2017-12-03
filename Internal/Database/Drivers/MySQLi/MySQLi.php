<?php namespace ZN\Database\Drivers;

use ZN\Database\Abstracts\DriverConnectionMappingAbstract;
use ZN\IndividualStructures\Support;
use ZN\ErrorHandling\Errors;
use stdClass;
use ZN\Database\Exception\ConnectionErrorException;


class MySQLiDriver extends DriverConnectionMappingAbstract
{
    //--------------------------------------------------------------------------------------------------------
    //
    // Author     : Ozan UYKUN <ozanbote@gmail.com>
    // Site       : www.znframework.com
    // License    : The MIT License
    // Copyright  : (c) 2012-2016, znframework.com
    //
    //--------------------------------------------------------------------------------------------------------

    //--------------------------------------------------------------------------------------------------------
    // Operators
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $operators =
    [
        'like' => '%'
    ];

    //--------------------------------------------------------------------------------------------------------
    // Statements
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $statements =
    [
        'autoincrement' => 'AUTO_INCREMENT',
        'primarykey'    => 'PRIMARY KEY',
        'foreignkey'    => 'FOREIGN KEY',
        'unique'        => 'UNIQUE',
        'null'          => 'NULL',
        'notnull'       => 'NOT NULL',
        'exists'        => 'EXISTS',
        'notexists'     => 'NOT EXISTS',
        'constraint'    => 'CONSTRAINT',
        'default'       => 'DEFAULT'
    ];

    //--------------------------------------------------------------------------------------------------------
    // Variable Types
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $variableTypes =
    [
        'int'           => 'INT',
        'smallint'      => 'SMALLINT',
        'tinyint'       => 'TINYINT',
        'mediumint'     => 'MEDIUMINT',
        'bigint'        => 'BIGINT',
        'decimal'       => 'DECIMAL',
        'double'        => 'DOUBLE',
        'float'         => 'FLOAT',
        'char'          => 'CHAR',
        'varchar'       => 'VARCHAR',
        'tinytext'      => 'TINYTEXT',
        'text'          => 'TEXT',
        'mediumtext'    => 'MEDIUMTEXT',
        'longtext'      => 'LONGTEXT',
        'date'          => 'DATE',
        'datetime'      => 'DATETIME',
        'time'          => 'TIME',
        'timestamp'     => 'TIMESTAMP'
    ];

    //--------------------------------------------------------------------------------------------------------
    // Var Types
    //--------------------------------------------------------------------------------------------------------
    //
    // @var array
    //
    //--------------------------------------------------------------------------------------------------------
    protected $types = 
    [
        0   => 'DECIMAL',      
        1   => 'TINY',
        2   => 'SHORT',
        3   => 'LONG',
        4   => 'FLOAT',
        5   => 'DOUBLE',
        6   => 'NULL',
        7   => 'TIMESTAMP',
        8   => 'LONGLONG',
        9   => 'INT24',
        10  => 'DATE',
        11  => 'TIME',
        12  => 'DATETIME',
        13  => 'YEAR',          
        14  => 'NEWDATE',     
        247 => 'ENUM',
        248 => 'SET',
        249 => 'TINY_BLOB',
        250 => 'MEDIUM_BLOB',
        251 => 'LONG_BLOG',
        252 => 'BLOB',
        253 => 'VAR_STRING',
        254 => 'STRING',
        255 => 'GEOMETRY'
    ];

    //--------------------------------------------------------------------------------------------------------
    // Construct
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function __construct()
    {
        Support::func('mysqli_connect', 'MySQLi');
    }

    //--------------------------------------------------------------------------------------------------------
    // Connect
    //--------------------------------------------------------------------------------------------------------
    //
    // @param array $config
    //
    //--------------------------------------------------------------------------------------------------------
    public function connect($config = [])
    {
        $this->config  = $config;

        $this->connect = mysqli_connect
        (
            $this->config['host'], 
            $this->config['user'],
            $this->config['password'], 
            $this->config['database']
        );

        if( empty($this->connect) )
        {
            throw new ConnectionErrorException('Database', 'connectError');
        }

        if( ! empty($this->config['charset']  ) ) $this->query("SET NAMES '".$this->config['charset']."'");  
        if( ! empty($this->config['charset']  ) ) $this->query('SET CHARACTER SET '.$this->config['charset']);  
        if( ! empty($this->config['collation']) ) $this->query('SET COLLATION_CONNECTION = "'.$this->config['collation'].'"');
    }

    //--------------------------------------------------------------------------------------------------------
    // Exec
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $query
    // @param array  $security
    //
    //--------------------------------------------------------------------------------------------------------
    public function exec($query, $security = NULL)
    {
        if( empty($query) )
        {
            return false;
        }

        return mysqli_query($this->connect, $query);
    }

    //--------------------------------------------------------------------------------------------------------
    // Query
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $query
    // @param array  $security
    //
    //--------------------------------------------------------------------------------------------------------
    public function query($query, $security = NULL)
    {
        return $this->query = $this->exec($query, $security);
    }

    //--------------------------------------------------------------------------------------------------------
    // Multi Query
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $query
    // @param array  $security
    //
    //--------------------------------------------------------------------------------------------------------
    public function multiQuery($query, $security = NULL)
    {
        if( empty($query) )
        {
            return false;
        }

        return $this->query = mysqli_multi_query($this->connect, $query);
    }

    //--------------------------------------------------------------------------------------------------------
    // Trans Start
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function transStart()
    {
        mysqli_autocommit($this->connect, false);

        return ( phpversion() > 5.5 )
               ? mysqli_begin_transaction($this->connect)
               : $this->query('START TRANSACTION');
    }

    //--------------------------------------------------------------------------------------------------------
    // Trans Roll Back
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function transRollback()
    {
        if( mysqli_rollback($this->connect) )
        {
            mysqli_autocommit($this->connect, true);

            return true;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Trans Commit
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function transCommit()
    {
        if( mysqli_commit($this->connect) )
        {
            mysqli_autocommit($this->connect, true);

            return true;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Insert ID
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function insertID()
    {
        if( ! empty($this->connect) )
        {
            return mysqli_insert_id($this->connect);
        }
        else
        {
            return false;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Column Data
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $column
    //
    //--------------------------------------------------------------------------------------------------------
    public function columnData($column)
    {
        if( empty($this->query) )
        {
            return false;
        }

        $columns   = [];
        $fieldData = mysqli_fetch_fields($this->query);
        $count     = count($fieldData);

        for( $i = 0; $i < $count; $i++ )
        {
            $fieldName = $fieldData[$i]->name;

            $columns[$fieldName]             = new stdClass();
            $columns[$fieldName]->name       = $fieldName;
            $columns[$fieldName]->type       = $this->types[$fieldData[$i]->type] ?? NULL;
            $columns[$fieldName]->maxLength  = $fieldData[$i]->max_length;
            $columns[$fieldName]->primaryKey = (int) ($fieldData[$i]->flags & 2);
            $columns[$fieldName]->default    = $fieldData[$i]->def;
        }

        return $columns[$column] ?? $columns;
    }

    //--------------------------------------------------------------------------------------------------------
    // Num Rows
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function numRows()
    {
        if( ! empty($this->query) )
        {
            return mysqli_num_rows($this->query);
        }
        else
        {
            return 0;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Columns
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function columns()
    {
        if( empty($this->query) )
        {
            return [];
        }

        $columns   = [];
        $fields    = mysqli_fetch_fields($this->query);
        $numFields = $this->numFields();

        for( $i = 0; $i < $numFields; $i++ )
        {
            $columns[] = $fields[$i]->name;
        }

        return $columns;
    }

    //--------------------------------------------------------------------------------------------------------
    // Num Fields
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function numFields()
    {
        if( ! empty($this->query) )
        {
            return mysqli_num_fields($this->query);
        }
        else
        {
            return 0;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Real Escape String
    //--------------------------------------------------------------------------------------------------------
    //
    // @param string $data
    //
    //--------------------------------------------------------------------------------------------------------
    public function realEscapeString($data)
    {
        if( ! empty($this->connect) )
        {
            return mysqli_real_escape_string($this->connect, $data);
        }
        else
        {
            return false;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Error
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function error()
    {
        if( ! empty($this->connect) )
        {
            return mysqli_error($this->connect);
        }
        else
        {
            return false;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Fetch Array
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function fetchArray()
    {
        if( ! empty($this->query) )
        {
            return mysqli_fetch_array($this->query);
        }
        else
        {
            return [];
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Fetch Assoc
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function fetchAssoc()
    {
        if( ! empty($this->query) )
        {
            return mysqli_fetch_assoc($this->query);
        }
        else
        {
            return [];
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Fetch Row
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function fetchRow()
    {
        if( ! empty($this->query) )
        {
            return mysqli_fetch_row($this->query);
        }
        else
        {
            return [];
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Affected Rows
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function affectedRows()
    {
        if( ! empty($this->connect) )
        {
            return mysqli_affected_rows($this->connect);
        }
        else
        {
            return 0;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Close
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function close()
    {
        if( ! empty($this->connect) )
        {
            mysqli_close($this->connect);
        }
        else
        {
            return false;
        }
    }

    //--------------------------------------------------------------------------------------------------------
    // Version
    //--------------------------------------------------------------------------------------------------------
    //
    // @param void
    //
    //--------------------------------------------------------------------------------------------------------
    public function version()
    {
        if( ! empty($this->connect) )
        {
            return mysqli_get_server_version($this->connect);
        }
        else
        {
            return false;
        }
    }
}
