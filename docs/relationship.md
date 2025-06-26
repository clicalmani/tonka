## Introduction

Database tables are often related to one another. For example, a blog post may have many comments or an order could be related to the user who placed it. **Elegant ORM** makes managing and working with these relationships easy, and supports a variety of common relationships:

- **One-to-One**: A single entity is associated with one other entity.
- **One-to-Many**: A single entity is associated with multiple entities.
- **Many-to-Many**: Multiple entities are associated with multiple other entities.

## Defining Relationships

To define relationships in **Elegant ORM**, you can use the following methods:

### One-to-One

To define a one-to-one relationship, use the `hasOne` and `belongsTo` methods:

```python
class User(Model):
    profile = hasOne('Profile')

class Profile(Model):
    user = belongsTo('User')
```

### One-to-Many

To define a one-to-many relationship, use the `hasMany` and `belongsTo` methods:

```python
class Post(Model):
    comments = hasMany('Comment')

class Comment(Model):
    post = belongsTo('Post')
```

### Many-to-Many

To define a many-to-many relationship, use the `belongsToMany` method:

```python
class User(Model):
    roles = belongsToMany('Role')

class Role(Model):
    users = belongsToMany('User')
```

These methods help you easily manage and query related data in your database.

## One-to-One / Has One

In a one-to-one relationship, each instance of a model is associated with one instance of another model. This type of relationship is useful when you want to split a large table into smaller tables for better organization and performance.

### Example

Consider a `User` and `Profile` relationship where each user has one profile:

```python
class User(Model):
    profile = hasOne('Profile')

class Profile(Model):
    user = belongsTo('User')
```

In this example, the `User` model has one `Profile`, and the `Profile` model belongs to one `User`. This setup allows you to easily access the profile of a user and vice versa.

### Database Schema

To implement this relationship in your database, you would typically have a foreign key in the `Profile` table that references the `User` table:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE profiles (
    id INT PRIMARY KEY,
    user_id INT,
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

This schema ensures that each profile is linked to a specific user, enforcing the one-to-one relationship at the database level.

## Defining the Inverse of the Relationship

In some cases, you may need to define the inverse of a relationship. This allows you to access the parent model from the related model. **Elegant ORM** makes this easy with the `belongsTo` method.

### Example

Consider the `User` and `Profile` relationship again. To define the inverse relationship, you would use the `belongsTo` method in the `Profile` model:

```python
class User(Model):
    profile = hasOne('Profile')

class Profile(Model):
    user = belongsTo('User')
```

In this example, the `Profile` model has a `belongsTo` relationship with the `User` model. This allows you to access the user associated with a profile.

### Database Schema

The database schema remains the same as previously defined:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE profiles (
    id INT PRIMARY KEY,
    user_id INT,
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

By defining the inverse relationship, you can easily navigate between related models, making your data management more efficient and intuitive.

## One to Many / Has Many

A one-to-many relationship is used to define relationships where a single model is the parent to one or more child models. For example, a blog post may have an infinite number of comments. Like all other Eloquent relationships, one-to-many relationships are defined by defining a method on your Eloquent model:

### Example

Consider a `Post` and `Comment` relationship where each post can have multiple comments:

```python
class Post(Model):
    comments = hasMany('Comment')

class Comment(Model):
    post = belongsTo('Post')
```

In this example, the `Post` model has many `Comment` instances, and each `Comment` belongs to one `Post`. This setup allows you to easily access all comments for a post and the post for a comment.

### Database Schema

To implement this relationship in your database, you would typically have a foreign key in the `Comment` table that references the `Post` table:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE comments (
    id INT PRIMARY KEY,
    post_id INT,
    content TEXT,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);
```

This schema ensures that each comment is linked to a specific post, enforcing the one-to-many relationship at the database level.

By defining these relationships, you can efficiently manage and query related data, making your application more robust and easier to maintain.

## One to Many (Inverse) / Belongs To

In a one-to-many relationship, you may need to define the inverse of the relationship to access the parent model from the child model. This is done using the `belongsTo` method.

### Example

Consider the `Post` and `Comment` relationship again. To define the inverse relationship, you would use the `belongsTo` method in the `Comment` model:

```python
class Post(Model):
    comments = hasMany('Comment')

class Comment(Model):
    post = belongsTo('Post')
```

In this example, the `Comment` model has a `belongsTo` relationship with the `Post` model. This allows you to access the post associated with a comment.

### Database Schema

The database schema remains the same as previously defined:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE comments (
    id INT PRIMARY KEY,
    post_id INT,
    content TEXT,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);
```

By defining the inverse relationship, you can easily navigate between related models, making your data management more efficient and intuitive.

## Many to Many / Belongs To Many

A many-to-many relationship is used to define relationships where multiple instances of a model are related to multiple instances of another model. For example, a user can have multiple roles, and a role can be assigned to multiple users. This type of relationship is defined using a pivot table.

### Example

Consider a `User` and `Role` relationship where each user can have multiple roles, and each role can be assigned to multiple users:

```python
class User(Model):
    roles = belongsToMany('Role')

class Role(Model):
    users = belongsToMany('User')
```

In this example, the `User` model has many `Role` instances, and each `Role` has many `User` instances. This setup allows you to easily access all roles for a user and all users for a role.

### Database Schema

To implement this relationship in your database, you would typically have a pivot table that references both the `User` and `Role` tables:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE roles (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE role_user (
    user_id INT,
    role_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    PRIMARY KEY (user_id, role_id)
);
```

This schema ensures that each user can be linked to multiple roles and each role can be linked to multiple users, enforcing the many-to-many relationship at the database level.

By defining these relationships, you can efficiently manage and query related data, making your application more robust and easier to maintain.

## Has One Through

A "has one through" relationship is used to define a one-to-one relationship that is accessed through an intermediate model. This type of relationship is useful when you want to access a related model through another related model.

### Example

Consider a `Supplier`, `User`, and `Profile` relationship where each supplier has one user, and each user has one profile. To define this relationship, you would use the `hasOneThrough` method:

```python
class Supplier(Model):
    profile = hasOneThrough('Profile', 'User')

class User(Model):
    profile = hasOne('Profile')
    supplier = belongsTo('Supplier')

class Profile(Model):
    user = belongsTo('User')
```

In this example, the `Supplier` model has one `Profile` through the `User` model. This setup allows you to easily access the profile of a supplier through the user.

### Database Schema

To implement this relationship in your database, you would typically have foreign keys in the `User` and `Profile` tables that reference the `Supplier` and `User` tables, respectively:

```sql
CREATE TABLE suppliers (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE users (
    id INT PRIMARY KEY,
    supplier_id INT,
    name VARCHAR(255),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE profiles (
    id INT PRIMARY KEY,
    user_id INT,
    bio TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

This schema ensures that each profile is linked to a specific user, and each user is linked to a specific supplier, enforcing the "has one through" relationship at the database level.

By defining these relationships, you can efficiently manage and query related data, making your application more robust and easier to maintain.

## Has Many Through

A "has many through" relationship is used to define a many-to-many relationship that is accessed through an intermediate model. This type of relationship is useful when you want to access related models through another related model.

### Example

Consider a `Country`, `User`, and `Post` relationship where each country has many users, and each user has many posts. To define this relationship, you would use the `hasManyThrough` method:

```python
class Country(Model):
    posts = hasManyThrough('Post', 'User')

class User(Model):
    posts = hasMany('Post')
    country = belongsTo('Country')

class Post(Model):
    user = belongsTo('User')
```

In this example, the `Country` model has many `Post` instances through the `User` model. This setup allows you to easily access all posts for a country through its users.

### Database Schema

To implement this relationship in your database, you would typically have foreign keys in the `User` and `Post` tables that reference the `Country` and `User` tables, respectively:

```sql
CREATE TABLE countries (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE users (
    id INT PRIMARY KEY,
    country_id INT,
    name VARCHAR(255),
    FOREIGN KEY (country_id) REFERENCES countries(id)
);

CREATE TABLE posts (
    id INT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    content TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

This schema ensures that each post is linked to a specific user, and each user is linked to a specific country, enforcing the "has many through" relationship at the database level.

By defining these relationships, you can efficiently manage and query related data, making your application more robust and easier to maintain.

## Polymorphic Relationships

Polymorphic relationships allow a model to belong to more than one other model on a single association. This is useful when a model can be associated with more than one type of model.

### Example

Consider a `Photo` model that can belong to either a `User` or a `Post` model. To define this relationship, you would use the `morphTo` and `morphMany` methods:

```python
class Photo(Model):
    imageable = morphTo()

class User(Model):
    photos = morphMany('Photo', 'imageable')

class Post(Model):
    photos = morphMany('Photo', 'imageable')
```

In this example, the `Photo` model can belong to either a `User` or a `Post`. The `User` and `Post` models can have many `Photo` instances.

### Database Schema

To implement this relationship in your database, you would typically have a polymorphic association with a type and id column:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE photos (
    id INT PRIMARY KEY,
    url VARCHAR(255),
    imageable_id INT,
    imageable_type VARCHAR(255)
);
```

This schema ensures that each photo can be linked to either a user or a post, enforcing the polymorphic relationship at the database level.

By defining these relationships, you can efficiently manage and query related data, making your application more flexible and easier to maintain.

## One to One (Polymorphic)

A one-to-one polymorphic relationship allows a model to belong to more than one other model on a single association. This is useful when a model can be associated with more than one type of model, but only one instance at a time.

### Example

Consider a `Tag` model that can belong to either a `Post` or a `Video` model. To define this relationship, you would use the `morphTo` and `morphOne` methods:

```python
class Tag(Model):
    taggable = morphTo()

class Post(Model):
    tag = morphOne('Tag', 'taggable')

class Video(Model):
    tag = morphOne('Tag', 'taggable')
```

In this example, the `Tag` model can belong to either a `Post` or a `Video`. The `Post` and `Video` models can have one `Tag` instance.

### Database Schema

To implement this relationship in your database, you would typically have a polymorphic association with a type and id column:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE videos (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    url VARCHAR(255)
);

CREATE TABLE tags (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    taggable_id INT,
    taggable_type VARCHAR(255)
);
```

This schema ensures that each tag can be linked to either a post or a video, enforcing the one-to-one polymorphic relationship at the database level.

By defining these relationships, you can efficiently manage and query related data, making your application more flexible and easier to maintain.

## One to Many (Polymorphic)

A one-to-many polymorphic relationship allows a model to belong to more than one other model on a single association. This is useful when a model can be associated with more than one type of model, and multiple instances at a time.

### Example

Consider a `Comment` model that can belong to either a `Post` or a `Video` model. To define this relationship, you would use the `morphTo` and `morphMany` methods:

```python
class Comment(Model):
    commentable = morphTo()

class Post(Model):
    comments = morphMany('Comment', 'commentable')

class Video(Model):
    comments = morphMany('Comment', 'commentable')
```

In this example, the `Comment` model can belong to either a `Post` or a `Video`. The `Post` and `Video` models can have many `Comment` instances.

### Database Schema

To implement this relationship in your database, you would typically have a polymorphic association with a type and id column:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE videos (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    url VARCHAR(255)
);

CREATE TABLE comments (
    id INT PRIMARY KEY,
    content TEXT,
    commentable_id INT,
    commentable_type VARCHAR(255)
);
```

This schema ensures that each comment can be linked to either a post or a video, enforcing the one-to-many polymorphic relationship at the database level.

By defining these relationships, you can efficiently manage and query related data, making your application more flexible and easier to maintain.

## One to Many (Polymorphic)

A one-to-many polymorphic relationship allows a model to belong to more than one other model on a single association. This is useful when a model can be associated with more than one type of model, and multiple instances at a time.

### Example

Consider a `Comment` model that can belong to either a `Post` or a `Video` model. To define this relationship, you would use the `morphTo` and `morphMany` methods:

```python
class Comment(Model):
    commentable = morphTo()

class Post(Model):
    comments = morphMany('Comment', 'commentable')

class Video(Model):
    comments = morphMany('Comment', 'commentable')
```

In this example, the `Comment` model can belong to either a `Post` or a `Video`. The `Post` and `Video` models can have many `Comment` instances.

### Database Schema

To implement this relationship in your database, you would typically have a polymorphic association with a type and id column:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE videos (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    url VARCHAR(255)
);

CREATE TABLE comments (
    id INT PRIMARY KEY,
    content TEXT,
    commentable_id INT,
    commentable_type VARCHAR(255)
);
```

This schema ensures that each comment can be linked to either a post or a video, enforcing the one-to-many polymorphic relationship at the database level.

By defining these relationships, you can efficiently manage and query related data, making your application more flexible and easier to maintain.

## Many to Many (Polymorphic)

A many-to-many polymorphic relationship allows a model to belong to more than one other model on a single association. This is useful when a model can be associated with more than one type of model, and multiple instances at a time.

### Example

Consider a `Tag` model that can belong to either a `Post` or a `Video` model. To define this relationship, you would use the `morphToMany` and `morphedByMany` methods:

```python
class Tag(Model):
    posts = morphToMany('Post', 'taggable')
    videos = morphToMany('Video', 'taggable')

class Post(Model):
    tags = morphedByMany('Tag', 'taggable')

class Video(Model):
    tags = morphedByMany('Tag', 'taggable')
```

In this example, the `Tag` model can belong to either a `Post` or a `Video`. The `Post` and `Video` models can have many `Tag` instances.

### Database Schema

To implement this relationship in your database, you would typically have a polymorphic association with a type and id column, along with a pivot table:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE videos (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    url VARCHAR(255)
);

CREATE TABLE tags (
    id INT PRIMARY KEY,
    name VARCHAR(255)
);

CREATE TABLE taggables (
    tag_id INT,
    taggable_id INT,
    taggable_type VARCHAR(255),
    FOREIGN KEY (tag_id) REFERENCES tags(id),
    PRIMARY KEY (tag_id, taggable_id, taggable_type)
);
```

This schema ensures that each tag can be linked to either a post or a video, enforcing the many-to-many polymorphic relationship at the database level.

By defining these relationships, you can efficiently manage and query related data, making your application more flexible and easier to maintain.

## Custom Polymorphic Types

In some cases, you may need to define custom polymorphic types to handle more complex relationships. **Elegant ORM** allows you to customize the polymorphic type column to suit your application's needs.

### Example

Consider a scenario where a `Like` model can belong to either a `Post`, `Comment`, or `Photo` model. To define this relationship with custom polymorphic types, you would use the `morphTo` and `morphMany` methods:

```python
class Like(Model):
    likeable = morphTo()

class Post(Model):
    likes = morphMany('Like', 'likeable')

class Comment(Model):
    likes = morphMany('Like', 'likeable')

class Photo(Model):
    likes = morphMany('Like', 'likeable')
```

In this example, the `Like` model can belong to a `Post`, `Comment`, or `Photo`. The `Post`, `Comment`, and `Photo` models can have many `Like` instances.

### Database Schema

To implement this relationship in your database, you would typically have a polymorphic association with a type and id column:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
);

CREATE TABLE comments (
    id INT PRIMARY KEY,
    content TEXT,
    post_id INT,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE TABLE photos (
    id INT PRIMARY KEY,
    url VARCHAR(255)
);

CREATE TABLE likes (
    id INT PRIMARY KEY,
    likeable_id INT,
    likeable_type VARCHAR(255)
);
```

This schema ensures that each like can be linked to a post, comment, or photo, enforcing the custom polymorphic relationship at the database level.

By defining custom polymorphic types, you can efficiently manage and query related data, making your application more flexible and easier to maintain.
## Querying Relations

**Elegant ORM** provides powerful methods to query relationships, allowing you to retrieve related data efficiently.

### Eager Loading

Eager loading helps to load related models along with the main model, reducing the number of queries executed.

#### Example

To load a user's profile along with the user:

```python
user = User.query().with('profile').find(1)
```

In this example, the `profile` relationship is loaded along with the `User` model, reducing the need for additional queries.

### Lazy Loading

Lazy loading retrieves related models only when they are accessed, which can be useful for optimizing performance in certain scenarios.

#### Example

To load a user's profile only when accessed:

```python
user = User.find(1)
profile = user.profile
```

In this example, the `profile` relationship is loaded only when it is accessed.

### Querying Related Models

You can query related models using dynamic properties and methods provided by **Elegant ORM**.

#### Example

To get all comments for a post:

```python
post = Post.find(1)
comments = post.comments
```

In this example, the `comments` relationship is used to retrieve all comments for the specified post.

### Filtering Related Models

You can apply filters to related models using query constraints.

#### Example

To get approved comments for a post:

```python
post = Post.find(1)
approved_comments = post.comments().where('approved', True).get()
```

In this example, the `comments` relationship is filtered to retrieve only approved comments.

By leveraging these querying techniques, you can efficiently manage and retrieve related data, making your application more performant and easier to maintain.
