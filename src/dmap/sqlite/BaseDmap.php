<?php
namespace PhpRbac\dmap\sqlite;

/**
 * Base data mapper for Permissions and Roles, since they are so similar.
 *
 * @author jamesvl
 * @author abiusx
 */
class BaseDmap extends \PhpRbac\dmap\mysql\BaseDmap {

    /**
     * Get the id of a [Role|Permission] given just its string Path.
     **/
    public function idFromPath($path)
    {
        $path = 'root' . $path;
        $Parts = explode("/", $path);

        $qry = "SELECT node.id, GROUP_CONCAT(parent.title, '/') AS path
                  FROM {$this->tblName} AS node,
                       {$this->tblName} AS parent
                 WHERE node.lft BETWEEN parent.lft AND parent.rght
                   AND node.title = ?
                 GROUP BY node.id
                HAVING path = ?
                ORDER BY parent.lft";

        $params = array($Parts[count($Parts) - 1], $path);

        $res = $this->_fetchRow($qry, $params);

        if ($res)
            return $res['id'];
        else
            return null;
    }

    public function resetAssignments()
    {
        $qry ="DELETE FROM {$this->pfx}rolepermissions";
        $res = $this->_execQuery($qry);

        $qry ="DELETE FROM sqlite_sequence
                WHERE name = ?";
        $res = $this->_execQuery($qry, array("{$this->pfx}rolepermissions"));
    }

    public function reset()
    {
        $qry = "DELETE FROM {$this->tblName}";
        $res = $this->_execQuery($qry);

        $qry =  "DELETE FROM sqlite_sequence
                 WHERE name = ?";
        $res = $this->_execQuery($qry, array($this->tblName));

        $qry = "INSERT INTO {$this->tblName}
                       (title, description, lft, rght)
                VALUES (?, ?, ?, ?)";

        $params = array('root', 'root', 0, 1);
        $res = $this->_execQuery($qry, $params);

        return $res;
    }
}
