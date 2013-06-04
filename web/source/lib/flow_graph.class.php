<?php

abstract class FlowGraph
{
    protected $edges;
    protected $nodes;
    protected $root_id;

    function __construct()
    {
        $this->edges = array();
        $this->nodes = array();
        $this->root_id = null;
    }

    // Add & set method
    public function add_to_edges($source_id, $target_id)
    {
        $this->edges[$source_id . '-' . $target_id] = array('id' => $source_id . '-vt-' . $target_id, 'source' => $source_id, 'target' => $target_id);
    }

    public function add_to_nodes($node)
    {
        $this->nodes[$node['id']] = $node;
    }

    public function add_node_id($id)
    {
        $this->add_to_nodes($this->wrap_node($id));
    }

    public function set_root_id($root_id)
    {
        $this->root_id = $root_id;
        $this->add_to_nodes($this->wrap_node($root_id));
    }

    public function set_graph($nodes, $edges)
    {
        $this->edges = $edges;
        $this->nodes = $nodes;
        $this->clean();
    }

    public function clear()
    {
        $this->edges = array();
        $this->nodes = array();
    }

    // Is & get methods

    public function is_direct_source($center_id, $source_id)
    {
        return !empty($this->edges[$source_id . '-' . $center_id]);
    }

    public function is_direct_target($center_id, $target_id)
    {
        return !empty($this->edges[$center_id . '-' . $target_id]);
    }

    public function get_source_node_ids($id)
    {
        $arr = aia_get_arrs_by_kv($this->edges, 'target', $id);
        return array_unique(aia_extract_vals_from_key($arr, 'source'));
    }

    public function get_target_node_ids($id)
    {
        $arr = aia_get_arrs_by_kv($this->edges, 'source', $id);
        return array_unique(aia_extract_vals_from_key($arr, 'target'));
    }

    public function get_edges()
    {
        return $this->edges;
    }

    public function get_nodes()
    {
        return $this->nodes;
    }

    public function get_node($id)
    {
        return $this->nodes[$id];
    }

    public function wrap_node($id)
    {
        return array('id' => $id);
    }

    public function get_root_id()
    {
        return $this->root_id;
    }

    public function  get_network_source_ids($target_id)
    {
        return array();
    }

    function walk_upstream($nodeid, $callback)
    {
        $processed = array();
        $this->walk_upstream_helper($nodeid, $callback, $processed);
    }

    public function walk_upstream_helper($node_id, $callback, &$processed)
    {

        $callback($node_id, $this);
        $processed[$node_id] = true;

        $source_ids = $this->get_upstream_ids($node_id);

        foreach ($source_ids as $source_id) {
            if ($processed[$source_id])
                continue;
            $this->walk_upstream_helper($source_id, $callback, $processed);
        }
    }

    public function get_upstream_ids($id)
    {
        return array();
    }

    public function build_network($root_id = null)
    {
        $root_id = $root_id ? $root_id : $this->root_id;
        if ($root_id === null)
            return;

        $root = $this->wrap_node($root_id);
        $this->add_to_nodes($root);
        //It store every walked footprint, it is global. If one node is being processed or has completed processing, it will in this array and we will never walk this node further.
        //We need this to avoid duplicated processing
        $processed = array();
        //It store  walked footprint in current flow(just a line, from current node to root node), it is not global.
        //If one node is being processed or has completed processing in current flow line, it will in this array and we will never walk this node further.
        //We need this to prevent circuit.
        $processed_stack = array();
        $this->build_network_helper($root, $processed_stack, $processed);
    }

    private function build_network_helper($root, $processed_stack, &$processed)
    {
        $root_id = $root['id'];
        $processed_stack[$root_id] = true;
        $processed[$root_id] = true;

        $source_ids = $this->get_network_source_ids($root_id);
        foreach ($source_ids as $source_id) {
            $source = $this->wrap_node($source_id);
            // source is already in the current flow line, if we go on regardless of it, circuit will occur
            if ($processed_stack[$source_id])
                continue;
            if ($this->can_build_edge($source, $root)) {
                $this->add_to_edges($source_id, $root_id);
            } else {
                continue;
            }

            //source is already processed or being processing
            if ($processed[$source_id])
                continue;

            $this->add_to_nodes($source);

            if ($this->can_continue_build_network($source, $root)) {
                $this->build_network_helper($source, $processed_stack, $processed);
            }
        }
    }

    protected function can_continue_build_network($source, $target)
    {
        return true;
    }

    protected function can_build_edge($source, $target)
    {
        return true;
    }


// DML methods
    public
    function purge_unused_nodes()
    {
        $new_nodes = array();
        foreach ($this->nodes as $node) {
            foreach ($this->edges as $edge) {
                if ($edge['target'] == $node['id'] || $edge['source'] == $node['id']) {
                    $new_nodes[] = $node;
                    break;
                }
            }
        }
        $this->nodes = $new_nodes;
    }

    public
    function purge_unused_edges()
    {
        $new_edges = array();
        foreach ($this->edges as $k => $edge) {
            $target_id = $edge['target'];
            $source_id = $edge['source'];
            if ($this->nodes[$target_id] && $this->nodes[$source_id]) {
                $new_edges[$k] = $edge;
            }
        }
        $this->edges = $new_edges;
    }

    public
    function clean()
    {
        $this->purge_unused_edges();
        $this->purge_unused_nodes();
        $this->clean_self_link_edges();
    }

    public
    function clean_self_link_edges()
    {
        $this->edges = array_filter($this->edges, function ($v) {
            return $v['source'] != $v['target'];
        });
    }

    public
    function merge_graph($compare)
    {
        $ok = false;
        while (!$ok) {
            $ok = true;
            foreach ($this->nodes as $node) {
                //get all source node ids
                $sources = $this->get_source_node_ids($node['id']);
                //get all target node ids
                $targets = $this->get_target_node_ids($node['id']);
                //filter surrounded same type nodes
                $surrounds = array();

                foreach (array_merge($targets, $sources) as $v) {
                    if ($compare($node['id'], $v, $this)) {
                        $ok = false;
                        $surrounds[] = $v;
                    }
                }

                //every in is to center in
                foreach ($surrounds as $surround_id) {
                    $remote_sources = aia_get_arrs_by_kv($this->edges, 'target', $surround_id);
                    foreach ($remote_sources as $k => $s) {
                        unset($this->edges[$k]);
                        $this->add_to_edges($s['source'], $node['id']);
                    }
                }

                //every out is from center out
                foreach ($surrounds as $surround_id) {
                    $remote_targets = aia_get_arrs_by_kv($this->edges, 'source', $surround_id);
                    foreach ($remote_targets as $k => $t) {
                        unset($this->edges[$k]);
                        $this->add_to_edges($node['id'], $t['target']);
                    }
                }
                //every inner surrounded edges is self link to center node
                $this->clean_self_link_edges();
            }
        }
    }


    public
    function filter_nodes($filt)
    {
        $new_nodes = array();
        foreach ($this->nodes as $k => $node) {
            if ($filt($node, $this->nodes, $this->edges)) {
                $new_nodes[$k] = $node;
            }
        }
        $this->nodes = $new_nodes;
        $this->purge_unused_edges();
    }

    public
    function filter_edges($filt)
    {
        $new_edges = array();
        foreach ($this->edges as $k => $edge) {
            if ($filt($edge, $this->nodes, $this->edges)) {
                $new_edges[$k] = $edge;
            }
        }
        $this->edges = $new_edges;
        $this->purge_unused_nodes();
    }


}


?>
