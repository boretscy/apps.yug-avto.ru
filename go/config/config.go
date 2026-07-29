package config

import (
	"github.com/joho/godotenv"
	"github.com/kelseyhightower/envconfig"
)

type Config struct {
	DBHost     string `envconfig:"DB_HOST" default:"localhost"`
	DBPort     string `envconfig:"DB_PORT" default:"3306"`
	DBUser     string `envconfig:"DB_USER" default:"admin_apps"`
	DBPassword string `envconfig:"DB_PASSWORD"`
	DBName     string `envconfig:"DB_NAME" default:"admin_apps"`

	APIAddr string `envconfig:"API_ADDR" default:":8080"`
	APIToken string `envconfig:"API_TOKEN"`

	AutoCRMBaseURL string `envconfig:"AUTOCRM_BASE_URL" default:"https://autos.autocrm.ru/api/v1"`
	AutoCRMAPIKey  string `envconfig:"AUTOCRM_API_KEY"`

	CalltouchBaseURL string `envconfig:"CALLTOUCH_BASE_URL" default:"https://api.calltouch.ru"`
	CalltouchAPIKey  string `envconfig:"CALLTOUCH_API_KEY"`

	TelegramBotToken string `envconfig:"TELEGRAM_BOT_TOKEN"`

	ImageUploadDir string `envconfig:"IMAGE_UPLOAD_DIR" default:"/var/www/admin/data/www/apps.avatr-yugavto.ru/upload/Cis"`
	ImageBaseURL   string `envconfig:"IMAGE_BASE_URL" default:"https://apps.avatr-yugavto.ru"`

	ProjectRoot    string `envconfig:"PROJECT_ROOT" default:"/var/www/admin/data/www/apps.avatr-yugavto.ru"`
	ONNXModelPath string `envconfig:"ONNX_MODEL_PATH"`
}

func Load() (*Config, error) {
	godotenv.Load()

	var cfg Config
	if err := envconfig.Process("", &cfg); err != nil {
		return nil, err
	}
	return &cfg, nil
}
