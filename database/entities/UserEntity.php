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
        nullable: false,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 191,
        nullable: false
    )]
    public VarChar $given_name;
    
    #[Property(
        length: 191,
        nullable: false
    )]
    public VarChar $family_name;

    #[Property(
        length: 200,
        nullable: false
    )]
    public VarChar $email;

    #[Property(
        length: 10,
        nullable: false
    )]
    public Char $phone;

    #[Property(
        nullable: false
    )]
    public Date $birth_date;

    #[Property(
        values: ['male', 'femail'],
        nullable: false,
        default: 'male'
    )]
    public Enum $gender;
}
