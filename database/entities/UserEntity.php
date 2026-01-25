<?php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Char;
use Clicalmani\Database\DataTypes\Date;
use Clicalmani\Database\DataTypes\Enum;
use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

class UserEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 191
    )]
    public VarChar $given_name;
    
    #[Property(
        length: 191
    )]
    public VarChar $family_name;

    #[Property(
        length: 200
    )]
    public VarChar $email;

    #[Property(
        length: 10
    )]
    public Char $phone;

    #[Property]
    public Date $birth_date;

    #[Property(
        values: ['male', 'femail'],
        default: 'male'
    )]
    public Enum|string $gender = 'male';
}
