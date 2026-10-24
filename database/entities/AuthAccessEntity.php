<?php
namespace Database\Entities;

use Clicalmani\Database\DataTypes\Integer;
use Clicalmani\Database\DataTypes\VarChar;
use Clicalmani\Database\Factory\Entity;
use Clicalmani\Database\Factory\Index;
use Clicalmani\Database\Factory\PrimaryKey;
use Clicalmani\Database\Factory\Property;

#[Index(
    name: 'userUNIQUE',
    key: 'user_id',
    unique: true
), Index(
    name: 'fk_authaccess_user1_idx',
    key: 'user_id',
    constraint: 'fk_authaccess_user1',
    references: \App\Models\User::class
)]
class AuthAccessEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        nullable: false,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 10,
        unsigned: true,
        nullable: false
    )]
    public Integer $user_id;

    #[Property(
        length: 192,
        nullable: false
    )]
    public ?VarChar $token;
}
