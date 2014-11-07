<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Topic extends Front_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('topic_m');
    }

    public function index()
    {
        $this->load->model('node_m');
        $data=$this->topic_m->get_topic_recent(NULL, 1, 20, 'recent', 2);

        $data['nodes']=$this->node_m->get_nodes();
        $data['site_title'] = '欢迎';

        $this->load->view('topic_list', $data);
    }

    /**
     * 最近主题列表
     * @param   $page 当前页码
     */
    public function recent($page = 1)
    {
        $data=$this->topic_m->get_topic_recent(NULL, $page, 20, 'recent', 2);

        if ($page>1) {
            $data['site_title'] = '最近的主题 '.$page.'/'.$data['num_pages'];
        }

        $this->load->view('topic_recent', $data);
    }

    /**
     * 查看主题详情
     * @param   $tid 帖子id
     */
    public function detail($tid)
    {
        $this->load->helper('form');
        $this->load->model('comment_m');

        $data['comments']=$this->comment_m->get_comments_bytid($tid);
        $data['topic']=$this->topic_m->get_topic_detail($tid);
        $data['site_title'] = $data['topic']['title'];
        $this->load->view('topic_detail', $data);

        //更新浏览计数
        $this->topic_m->update($tid, array('view'=>$data['topic']['view']+1));
    }

    /**
     * 添加新主题
     */
    public function add()
    {
        $this->load->helper('auth');
        is_login_exit();

        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->model('node_m');

        $this->form_validation->set_rules('title', 'Title', 'trim|required|min_length[4]');
        $this->form_validation->set_rules('nid', 'Node', 'required');
        $this->form_validation->set_rules('content');

        if ($this->form_validation->run() == FALSE)
        {
            //form failed
            $data['nodes']=$this->node_m->get_nodes();
            $data['site_title'] = '创建新主题';
            $this->load->view('topic_add', $data);
        }
        else
        {
            //form success
            $data = array(
                'nid' => $this->input->post('nid'),
                'uid' => $this->session->userdata('uid'),
                'title' => strip_tags($this->input->post('title', TRUE)),
                'content' => $this->input->post('content'),
                'addtime' => time(),
                'replytime' => time()
            );

            $insert_id = $this->topic_m->add($data);
            redirect('topic/'.$insert_id);
        }
    }
}

/* End of file topic.php */
/* Location: ./application/controllers/topic.php */