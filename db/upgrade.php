<?php

function xmldb_tool_createcourse_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager();
    
    if($oldversion < 2014100205) {
        $table = new xmldb_table('tool_createcourse');
        
        //Adding fields to table
        $table->add_field('id', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('termcode', XMLDB_TYPE_INTEGER, '6', null, XMLDB_NOTNULL, null, null);
        $table->add_field('suffix', XMLDB_TYPE_CHAR, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        
        //Adding keys to table
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        //Adding indices to table tool_createcourse
        $table->add_index('mdl_toolcreate_trm_ix', XMLDB_INDEX_UNIQUE, array('termcode'));
        $table->add_index('mdl_toolcreate_suf_ix', XMLDB_INDEX_UNIQUE, array('suffix'));
        $table->add_index('mdl_toolcreate_cid_ix', XMLDB_INDEX_UNIQUE, array('categoryid'));
        
        //$dbman->create_table($table);
        
        upgrade_plugin_savepoint(true, 2014100205, 'tool', 'createcourse');
    }
    
    return true;
}