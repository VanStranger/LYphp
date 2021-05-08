<?php
    return [
        "app_path"=>"application",
        "default_module"=>"index",
        "default_controller"=>"index",
        "default_action"=>"index",
        "path_type"=>0,
        'template'  => [
            'type'   => 'ly',
            'tpl_begin' =>    '{{',
            'tpl_end'   =>    '}}'
        ],
        'params_strict'=>0,//0 无传参无默认值，使用null，1 无传参使用默认值
        "PRODUCTION_MODE"=>false,//生产模式启用缓存，开发模式不启用
    ];