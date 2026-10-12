package models

// PengaturanSistem maps to `pengaturan_sistem` table.
// Key-value store for system settings.
type PengaturanSistem struct {
	ID       uint   `gorm:"primaryKey;column:id" json:"id"`
	KeyName  string `gorm:"column:key_name;uniqueIndex" json:"key_name"`
	Value    string `gorm:"column:value;type:text" json:"value"`
	Label    string `gorm:"column:label" json:"label,omitempty"`
	Category string `gorm:"column:category" json:"category,omitempty"`
}

func (PengaturanSistem) TableName() string {
	return "pengaturan_sistem"
}
