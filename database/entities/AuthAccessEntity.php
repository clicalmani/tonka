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
    references: \App\Models\User::class,
    onDelete: Index::ON_DELETE_CASCADE,
    onUpdate: Index::ON_UPDATE_CASCADE
)]
class AuthAccessEntity extends Entity
{
    #[Property(
        length: 10,
        unsigned: true,
        autoIncrement: true
    ), PrimaryKey]
    public Integer $id;

    #[Property(
        length: 10,
        unsigned: true
    )]
    public Integer $user_id;

    #[Property(
        length: 64
    )]
    public VarChar $token;
}
