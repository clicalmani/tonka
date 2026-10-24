<?php 
namespace App\Models;

use Clicalmani\Database\Traits\HasFactory;
use Clicalmani\Core\Acme\Model;

class User extends Model
{
    use HasFactory;
    
    /**
     * Model database table 
     *
     * @var string $table Table name
     */
    protected string $table = "users AS u";

    /**
     * Model entity
     * 
     * @var string
     */
    protected string $entity = \Database\Entities\UserEntity::class;

    /**
     * Table primary key(s)
     * Use an array if the key is composed with more than one attributes.
     *
     * @var string|array $primary_keys Table primary key.
     */
    protected string|array $primaryKey = "id";

    protected array $hidden = ['password'];

    /**
     * Constructor 
     *
     * @param mixed $id
     */
    public function __construct(mixed $id = null)
    {
        parent::__construct($id);
    }
}
