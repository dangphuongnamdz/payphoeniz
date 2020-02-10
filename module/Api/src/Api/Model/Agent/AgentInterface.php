<?php
namespace Api\Model\Agent;

interface AgentInterface
{
	//function getConfig();
    function getServer($agent);
    function getRole(Array $dataInput, $config);
    function exchange(Array $dataInput, $config, $key);
}