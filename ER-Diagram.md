```mermaid
erDiagram
  books ||--o{ users : "1つの書籍情報は1つの作成者IDを持つ"
  books ||--o{ book_genre : "複数の書籍情報は1以上のジャンルを持つ"
  genres ||--o{ book_genre : "複数のジャンルは1以上の書籍情報を持つ"
  users ||--o{ user_book : "１つのユーザーIDは複数の書籍情報を持つ"
  books ||--o{ user_book : "複数の書籍情報は1つのユーザーIDを持つ"
  users ||--o{ user_review : "１つのユーザーは１つのレビューを持つ"
  reviews ||--|| user_review : "１つのレビューは１つのユーザーIDを持つ"

  users {
    bigint          id                  PK
    varchar(255)    name
    varchar(255)    email
    timestamp       email_verified_at
    varchar(100)    remenber_token
    timestamp       created_at
    timestamp       updated_at
  }

  books {
    bigint          id                  PK
    varchar(255)    title
    varchar(255)    author
    varchar(13)     isbn
    timestamp       published_date
    string          description
    string          image_url
    bigint          user_id             FK
    timestamp       created_at
    timestamp       updated_at
  }

  genres {
    bigint          id                  PK
    varchar(50)     name
    timestamp       created_at
    timestamp       updated_at
  }

  reviews {
    bigint          id                  PK
    bigint          posted_id           FK
    bigint          book_id             FK
    tinyint         evaluation_value
    varchar(255)    comment
    timestamp       created_at
    timestamp       updated_at
  }

  book_genre {
    bigint          id                  PK
    bigint          book_id             FK
    bigint          genre_id            FK
    timestamp       created_at
    timestamp       updated_at
  }

  user_book {
    bigint          id                  PK
    bigint          user_id             FK
    bigint          book_id             FK
    timestamp       created_at
    timestamp       updated_at
  }

  user_review {
    bigint          id                  PK
    bigint          user_id             FK
    bigint          review_id           FK
    timestamp       created_at
    timestamp       updated_at
  }

  password_reset_tokens {
    string          email               PK
    string          token
    timestamp       created_at
  }

```
