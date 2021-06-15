#cd ./packages/siminia
#git reset --hard
#git pull origin customize/2980-basmajiramez@gmail.com-Bianca-Nera
#git checkout customize/2980-basmajiramez@gmail.com-Bianca-Nera
#git pull
#yarn install
#yarn run build
#cd ../../
#cp -f .env-file ./packages/siminia/.env
#yarn install
#yarn run build
pm2 delete biancasiminia
NODE_ENV=production PORT=1402 pm2 start --name biancasiminia  npm -- run stage:siminia
#curl -v https://bianca-nera.com/magento/simiconnector/index/updatepwaversion
