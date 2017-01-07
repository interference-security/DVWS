<?php

namespace React\MySQL;

class Query
{
    private $sql;

    private $builtSql;

    private $params = [];

    private $escapeChars = array(
            "\x00"   => "\\0",
            "\r"   => "\\r",
            "\n"   => "\\n",
            "\t"   => "\\t",
            //"\b"   => "\\b",
            //"\x1a" => "\\Z",
            "'"    => "\'",
            '"'    => '\"',
            "\\"   => "\\\\",
            //"%"    => "\\%",
            //"_"    => "\\_",
        );

    public function __construct($sql)
    {
        $this->sql = $sql;
    }

    /**
     * Binding params for the query, mutiple arguments support.
     *
     * @param  mixed              $param
     * @return \React\MySQL\Query
     */
    public function bindParams()
    {
        $this->builtSql = null;
        $this->params   = func_get_args();

        return $this;
    }

    public function bindParamsFromArray(array $params)
    {
        $this->builtSql = null;
        $this->params   = $params;

        return $this;
    }

    /**
     * Binding params for the query, mutiple arguments support.
     *
     * @param  mixed              $param
     * @return \React\MySQL\Query
     *                                  @deprecated
     */
    public function params()
    {
        $this->params   = func_get_args();
        $this->builtSql = null;

        return $this;
    }

    public function escape($str)
    {
        return strtr($str, $this->escapeChars);
    }

    /**
     * @param  mixed  $value
     * @return string
     */
    protected function resolveValueForSql($value)
    {
        $type = gettype($value);
        switch ($type) {
            case 'boolean':
                $value = (int) $value;
                break;
            case 'double':
            case 'integer':
                break;
            case 'string':
                $value = "'" . $this->escape($value) . "'";
                break;
            case 'array':
                $nvalue = [];
                foreach ($value as $v) {
                    $nvalue[] = $this->resolveValueForSql($v);
                }
                $value = implode(',', $nvalue);
                break;
            case 'NULL':
                $value = 'NULL';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Not supportted value type of %s.', $type));
                break;
        }

        return $value;
    }

    protected function buildSql()
    {
        $sql = $this->sql;

        $offset = strpos($sql, '?');
        foreach ($this->params as $param) {
            $replacement = $this->resolveValueForSql($param);
            $sql = substr_replace($sql, $replacement, $offset, 1);
            $offset = strpos($sql, '?', $offset + strlen($replacement));
        }
        if ($offset !== false) {
            throw new \LogicException('Params not enouth to build sql');
        }

        return $sql;
        /*
        $names    = array();
        $inName   = false;
        $currName = '';
        $currIdx  = 0;
        $sql      = $this->sql;
        $len      = strlen($sql);
        $i        = 0;
        do {
            $c    = $sql[$i];
            if ($c === '?') {
                $names[$i] = $c;
            } elseif ($c === ':') {
                $currName .= $c;
                $currIdx  = $i;
                $inName   = true;
            } elseif ($c === ' ') {
                $inName   = false;
                if ($currName) {
                    $names[$currIdx] = $currName;
                    $currName = '';
                }
            } else {
                if ($inName) {
                    $currName .= $c;
                }
            }
        } while (++ $i < $len);

        if ($inName) {
            $names[$currIdx] = $currName;
        }

        $namedMarks = $unnamedMarks = array();
        foreach ($this->params as $arg) {
            if (is_array($arg)) {
                $namedMarks += $arg;
            } else {
                $unnamedMarks[] = $arg;
            }
        }

        $offset = 0;
        foreach ($names as $idx => $value) {
            if ($value === '?') {
                $replacement = array_shift($unnamedMarks);
            } else {
                $replacement = $namedMarks[$value];
            }
            list($arg, $len) = $this->getEscapedStringAndLen($replacement);
            $sql = substr_replace($sql, $arg, $idx + $offset, strlen($value));
            $offset += $len - strlen($value);
        }

        return $sql;
        */
    }

    /**
     * Get the constructed and escaped sql string.
     *
     * @return string
     */
    public function getSql()
    {
        if ($this->builtSql === null) {
            $this->builtSql = $this->buildSql();
        }

        return $this->builtSql;
    }
}
