package models

import "time"

// User maps to the `users` table in the existing PHP app.
// Only fields needed for auth & AI chat are included.
// More fields will be added as other modules are rewritten.
type User struct {
	ID          uint      `gorm:"primaryKey;column:id_user" json:"id"`
	IDRef       uint      `gorm:"column:id_ref" json:"id_ref"`
	Username    string    `gorm:"column:username" json:"username"`
	Password    string    `gorm:"column:password" json:"-"`
	NamaLengkap string    `gorm:"column:nama_lengkap" json:"nama_lengkap"`
	Role        string    `gorm:"column:role" json:"role"`
	Email       string    `gorm:"column:email" json:"email,omitempty"`
	Status      string    `gorm:"column:status" json:"status"`
	CreatedAt   time.Time `gorm:"column:created_at" json:"created_at"`
	UpdatedAt   time.Time `gorm:"column:updated_at" json:"updated_at"`
}

func (User) TableName() string {
	return "users"
}
