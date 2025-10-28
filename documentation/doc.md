# 🧠 **Eloquent Mastery: 2.1.1 → 2.1.3**


---

## **CHAPTER 1: 2.1.1 — Migrations & Eloquent Models**
### 🎯 Goal: Create the database structure and PHP models for `User`, `Article`, `Tag`, and the pivot table.

### 📦 What You Get
- 4 database tables  
- 3 Eloquent models  
- Foreign keys + constraints  
- Ready for relationships  

---

### **Step-by-Step Code (with comments)**

#### 1. Create Migration Files
```bash
# Create migrations
php artisan make:migration create_articles_table
php artisan make:migration create_tags_table
php artisan make:migration create_article_tag_table

# Create models
php artisan make:model Article
php artisan make:model Tag
````

---

#### 2. Migration: `create_articles_table.php`

```php
Schema::create('articles', function (Blueprint $table) {
    $table->id(); // Primary key

    // Foreign key to users table
    $table->foreignId('user_id')
          ->constrained()        // Links to users.id
          ->cascadeOnDelete();   // Delete articles if user deleted

    $table->string('title', 180);
    $table->string('slug', 200)->unique();  // Unique URL slug
    $table->text('excerpt')->nullable();
    $table->longText('content')->nullable();
    $table->timestamps();
});
```

---

#### 3. Migration: `create_tags_table.php`

```php
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->timestamps();
});
```

---

#### 4. Migration: `create_article_tag_table.php` (Pivot)

```php
Schema::create('article_tag', function (Blueprint $table) {
    $table->foreignId('article_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

    // Prevent duplicate combinations
    $table->primary(['article_id', 'tag_id']);
});
```

---

#### 5. Model: `app/Models/Article.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'content'
    ];
}
```

---

#### 6. Model: `app/Models/Tag.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];
}
```

---

#### 7. Run Migrations

```bash
php artisan migrate
```

---

#### ✅ Verification in Tinker

```bash
php artisan tinker
>>> App\Models\Article::count(); // 0
>>> App\Models\Tag::create(['name' => 'Test', 'slug' => 'test']);
```

✅ **Database structure ready!**

---

## **CHAPTER 2: 2.1.2 — Eloquent Relationships**

### 🎯 Goal: Connect models so you can navigate data easily.

### 📦 What You Get

* `User → Articles` (1-n)
* `Article ↔ Tag` (n-n)
* Eager loading
* Relationship counts

---

### **Code with Comments**

#### 1. 1-n: User has many Articles

**`app/Models/User.php`**

```php
public function articles()
{
    return $this->hasMany(Article::class);
}
```

**`app/Models/Article.php`**

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

---

#### 2. n-n: Article ↔ Tag

**`app/Models/Article.php`**

```php
public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

**`app/Models/Tag.php`**

```php
public function articles()
{
    return $this->belongsToMany(Article::class);
}
```

---

#### 3. Eager Loading (Avoid N+1)

```php
// BAD
$articles = App\Models\Article::all();
foreach ($articles as $a) { $a->user; }

// GOOD
$articles = App\Models\Article::with(['user', 'tags'])->get();
```

---

#### 4. Count Related Records

```php
$articles = App\Models\Article::withCount('tags')->get();

foreach ($articles as $a) {
    echo $a->title . " ({$a->tags_count} tags)";
}
```

---

#### 5. Test in Tinker

```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->articles;

>>> $article = App\Models\Article::first();
>>> $article->user->name;
>>> $article->tags->pluck('name');

>>> App\Models\Tag::first()->articles;
```

✅ **Relationships fully functional!**

---

## **CHAPTER 3: 2.1.3 — Seeders & Factories**

### 🎯 Goal: Auto-generate 5 users, 20 articles, 10 tags with working links.

### 📦 What You Get

* Realistic fake data
* Relationships populated
* Single command setup

---

### **Step-by-Step Code**

#### 1. Create Factories

```bash
php artisan make:factory UserFactory --model=User
php artisan make:factory TagFactory --model=Tag
php artisan make:factory ArticleFactory --model=Article
```

---

#### 2. Factory: `UserFactory.php`

```php
public function definition(): array
{
    return [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'password' => bcrypt('password'),
        'remember_token' => Str::random(10),
    ];
}
```

---

#### 3. Factory: `TagFactory.php`

```php
$name = fake()->unique()->word();

return [
    'name' => ucfirst($name),
    'slug' => Str::slug($name),
];
```

---

#### 4. Factory: `ArticleFactory.php`

```php
$title = fake()->unique()->sentence(4);

return [
    'user_id' => User::inRandomOrder()->value('id') ?? 1,
    'title' => $title,
    'slug' => Str::slug($title),
    'excerpt' => fake()->sentence(12),
    'content' => fake()->paragraphs(3, true),
];
```

---

#### 5. Create Seeders

```bash
php artisan make:seeder UserSeeder
php artisan make:seeder TagSeeder
php artisan make:seeder ArticleSeeder
php artisan make:seeder PivotArticleTagSeeder
```

---

#### 6. Seeder: `UserSeeder.php`

```php
public function run(): void
{
    User::factory()->count(5)->create();
}
```

---

#### 7. Seeder: `TagSeeder.php`

```php
Tag::factory()->count(10)->create();
```

---

#### 8. Seeder: `ArticleSeeder.php`

```php
Article::factory()->count(20)->create();
```

---

#### 9. Seeder: `PivotArticleTagSeeder.php`

```php
public function run(): void
{
    $tagIds = Tag::pluck('id');

    Article::all()->each(function ($article) use ($tagIds) {
        $article->tags()->sync(
            $tagIds->random(rand(1, 4))->all()
        );
    });
}
```

---

#### 10. Final `DatabaseSeeder.php`

```php
public function run(): void
{
    $this->call([
        UserSeeder::class,
        TagSeeder::class,
        ArticleSeeder::class,
        PivotArticleTagSeeder::class,
    ]);
}
```

---

#### 11. Run Everything

```bash
php artisan migrate:fresh --seed --verbose
```

**Output:**

```
Seeding: UserSeeder
Seeding: TagSeeder
Seeding: ArticleSeeder
Seeding: PivotArticleTagSeeder
```

---

#### 12. Final Test

```bash
php artisan tinker
>>> App\Models\User::count();     // 5
>>> App\Models\Article::count();  // 20
>>> App\Models\Tag::count();      // 10

>>> App\Models\User::first()->articles->count();  // 3–6
>>> App\Models\Article::first()->tags->pluck('name');
```

---

## ✅ **SUMMARY: What You Now Have**

| Feature          | Status        | Command                |
| ---------------- | ------------- | ---------------------- |
| Tables           | ✅ Created     | `migrate`              |
| Models           | ✅ Ready       | `make:model`           |
| 1-n Relationship | ✅ Working     | `$user->articles`      |
| n-n Relationship | ✅ Working     | `$article->tags`       |
| Fake Data        | ✅ 5 + 20 + 10 | `migrate:fresh --seed` |

---

### 🚀 **Your Final Workflow**

```bash
# 1. Reset + Fill DB
php artisan migrate:fresh --seed

# 2. Test
php artisan tinker
>>> App\Models\User::first()->articles
>>> App\Models\Article::first()->tags->pluck('name')
```

✅ You are **100% ready for 2.1.4 (CRUD)**.
Say **“Next”** or **“Code only”** to continue.
