<?php
/**
 * User: freefcw
 * Date: 12-1-10
 * Time: 上午1:16
 */
class Model_Solution extends Model_Base
{
    static $table = 'solution';
    static $primary_key = 'solution_id';

    static $cols = array(
        'solution_id',
        'problem_id',
        'user_id',
        'time',
        'memory',
        'in_date',
        'className',
        'result',
        'language',
        'ip',
        'contest_id',
        'valid',
        'num',
        'code_length',
        'judgetime',
    );

    public $solution_id;
    public $problem_id;
    public $user_id;
    public $time;
    public $memory;
    public $in_date;
    public $className;
    public $result;
    public $language;
    public $ip;
    public $contest_id;
    public $valid;
    public $num;
    public $code_length;
    public $judgetime;
    /**
     * @param $var
     *
     * @return bool
     */
    private function is_search($var)
    {
        return ($var != -1 AND $var !== null AND $var !== '');
    }

    /**
     * @param int    $page_id
     * @param        $problem_id
     * @param string $user_id
     * @param null   $cid
     * @param        $language
     * @param        $result
     *
     * @return array
     */public function get_status($page_id = 1, $problem_id = -1, $user_id = '', $cid = null, $language = -1, $result = -1)
    {
        //TODO: move to solutions
        $condition = array();
        if ($this->is_search($problem_id))
            $condition['problem_id'] = intval($problem_id);
        if ($this->is_search($user_id))
            $condition['user_id'] = $user_id;
        if ($this->is_search($language))
            $condition['language'] = intval($language);
        if ($this->is_search($result))
            $condition['result'] = intval($result);
        if ($this->is_search($cid))
            $condition['contest_id'] = intval($cid);

        $need = array('solution_id', 'problem_id', 'user_id', 'time', 'memory', 'language', 'result', 'code_length', 'add_date');

        $per_page = 20;
        $ret = $this->collection->find($condition, $this->i_need($need))
            ->sort(array('solution_id' => -1))
            ->limit($per_page)
            ->skip(($page_id - 1) * $per_page);

        return iterator_to_array($ret);
    }

    /**
     * @param $pid
     *
     * @return array
     */
    public static function get_summary($pid)
    {
        # TODO: add content
        $data = array();
        $pid = intval($pid);
        // get total solutions

        $condition = array('problem_id' => $pid);
        $data['total'] = $this->collection->find($condition)->count();

        // get total user has submited
        $result = $this->db->command(array('distinct' => 'solution', 'key' => 'user_id', 'query' => $condition));
        $data['submit_user'] = count($result['values']);

        // get total user has ac
        $condition = array('problem_id' => $pid, 'result' => 4);
        $result = $this->db->command(array('distinct' => 'solution', 'key' => 'user_id', 'query' => $condition));
        //no this method:$result = $collection->find($condition)->distinct('user_id');
        $data['ac_user'] = count($result['values']);

        // get all status
        $data['more'] = array();
        for($i = 4; $i <= 11; $i++)
        {
            $condition['result'] = $i;
            $ret = $this->collection->find($condition)->count();
            $data['more'][$i] = $ret;
        }
        return $data;
    }

    /**
     * @param     $pid
     * @param int $start
     * @param int $limit
     *
     * @return array
     */
    public static function best_solution($pid, $start = 0, $limit = 20)
    {
        # TODO: add content

        $condition = array('result'=>4);
        $need = array('solution_id', 'user_id', 'language', 'memory', 'add_date', 'time', 'score');

        //$ret = $collection->find($condition, $this->i_need($need))->sort('score')->sort($this->i_need(array('score', 'add_date')));
        $ret = $this->collection->find($condition, $this->i_need($need))->sort(array('time'=>1, 'memory'=>1))->limit(50);

		$sql 	= "SELECT solution_id, count(*) att, user_id, language, memory, time, min(10000000000000000000 + time *100000000000 + memory *100000 + code_length) score, in_date
					FROM solution
					WHERE result = 4
					GROUP BY user_id
					ORDER BY score, in_date
					LIMIT $start, $limit";


        return iterator_to_array($ret);
    }

    /**
     * @param string $problem_id
     * @param string $user_id
     * @param        $language
     * @param        $result
     *
     * @return int
     */public function get_status_count($problem_id = '', $user_id = '', $language = -1, $result = -1)
	{
        //TODO: move to solutions
        $condition = array();
        if (! is_null($problem_id)) $condition['problem_id'] = $problem_id;
        if (! is_null($user_id)) $condition['user_id'] = $user_id;
        if (! is_null($language)) $condition['language'] = $language;
        if (! is_null($result)) $condition['result'] = $result;
        //if (! is_null($cid)) $condition['cid'] = $cid;

        $ret = $this->collection->count($condition);
        return $ret;
	}

    /**
     * @param $post
     */
    public function new_solution($post)
    {
        // contest solution or normal solution
        $new_doc = array(
            'p_id' => intval($post['pid']),
            'user_id' => $post['user_id'],
            'add_date' => new MongoDate(time()),
            'language' => intval($post['language']),
            'ip' => Request::$client_ip,
            'code_length' => strlen($post['source']),
            'contest_id' => intval($post['cid']),
            'num' => intval($post['num']),
            'source' => $post['source'],
        );

        //$collection->save($new_doc);
    }

    /**
     * @param $id
     */
    public function rejudge_solution($id)
    {
        $condition = array('solution_id' => $id);
        $changes = array('$set' => array('result' => 1));
        $this->collection->update($condition, $changes);
    }

    /**
     * @param $id
     */
    public function rejudge_problem($id)
    {
        $condition = array('problem_id' => $id);
        $changes = array('$set' => array('result' => 1));
        $options = array('multiple' => true);
        $this->collection->update($condition, $changes, $options);
    }

    protected function initial_data()
    {}

    public function validate()
    {}
}