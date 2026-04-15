<?php


namespace Company\User\Model;


use Company\SQL\Mapper;

class UserLoginMapper extends Mapper
{

    function tableName()
    {
        return "user_login";
    }

    function tableAlias()
    {
        return "user_login";
    }

    function getPkField()
    {
        return "userID";
    }
}